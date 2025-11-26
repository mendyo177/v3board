<?php

namespace App\Http\Controllers\V1\Admin\Server;

use App\Http\Controllers\Controller;
use App\Models\ServerV2node;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class CustomNodesController extends Controller
{
    public function page(Request $request)
    {
        $licenseValid = LicenseService::c();
        return view('plugins.custom_nodes.import', [
            'secure_path' => config('v2board.secure_path', config('v2board.frontend_admin_path', hash('crc32b', config('app.key')))),
            'license_valid' => $licenseValid,
        ]);
    }

    public function activate(Request $request)
    {
        $params = $request->validate([
            'license' => 'required|string'
        ]);
        $license = trim($params['license']);
        if (LicenseService::v($license)) {
            if (LicenseService::w($license)) {
                return response()->json(['s' => true, 'm' => '激活成功', 'd' => null]);
            } else {
                return response()->json(['s' => false, 'm' => '激活失败：无法写入许可证文件', 'd' => null], 500);
            }
        } else {
            return response()->json(['s' => false, 'm' => '激活失败：许可证无效', 'd' => null], 403);
        }
    }

    public function import(Request $request)
    {
        $params = $request->validate([
            'group_id' => 'nullable|string',
            'route_id' => 'nullable|string',
            'raw' => 'required|string',
            'prefix' => 'nullable|string',
            'rate' => 'nullable',
            'show' => 'nullable|in:0,1',
            'sort' => 'nullable',
            'tags' => 'nullable|string',
        ]);
        $groupId = $this->csvToArray($params['group_id'] ?? '') ?: [4];
        $routeId = $this->csvToArray($params['route_id'] ?? '');
        $tags = $this->csvToArray($params['tags'] ?? '');
        $rate = (string)($params['rate'] ?? '1');
        $show = isset($params['show']) ? (int)$params['show'] : 1;
        $sort = $params['sort'] ?? null;
        $prefix = array_key_exists('prefix', $params) ? (string)$params['prefix'] : "☁️-";
        $lines = preg_split('/\r?\n/', trim($params['raw']));
        $created = 0; $errors = [];
        $baseSort = ($sort !== null && $sort !== '') ? (int)$sort : 6000;
        foreach ($lines as $i => $line) {
            $line = trim($line); if ($line === '') { continue; }
            $meta = $this->parseLinkMeta($line); $now = time();
            $baseName = $meta['name'] ?: ($meta['host'] ?: '自定义节点');
            $displayName = ($prefix !== '') ? ($prefix . $baseName) : $baseName;
            $record = [
                'group_id' => $groupId,
                'route_id' => $routeId,
                'name' => $displayName,
                'host' => $meta['host'] ?: '127.0.0.1',
                'port' => (string)($meta['port'] ?: 443),
                'server_port' => (int)($meta['port'] ?: 443),
                'tags' => array_values(array_filter($tags, fn($v) => $v !== null && $v !== '')),
                'rate' => $rate,
                'show' => $show,
                'protocol' => 'vmess',
                'tls' => 1,
                'network' => 'tcp',
                'network_settings' => ['raw_uri' => $line],
                'up_mbps' => 0,
                'down_mbps' => 0,
                'sort' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            try {
                $currentSort = $baseSort + $created;
                while (ServerV2node::find($currentSort)) { $currentSort++; }
                $record['sort'] = $currentSort;
                $node = new ServerV2node($record);
                $node->id = $currentSort; $node->sort = $currentSort; $node->save();
                $created++;
            } catch (\Exception $e) {
                $errors[] = '第' . ($i + 1) . '行 创建失败：' . $e->getMessage();
                continue;
            }
        }
        $failed = count($errors);
        $status = "已创建 {$created} 条自定义节点；失败 {$failed} 条；起始排序为 {$baseSort}，同批按行递增且 id=sort";
        return redirect()->back()->with(['status' => $status, 'errors' => $errors]);
    }

    public function deleteAll(Request $request)
    {
        $deleted = 0; $all = ServerV2node::get();
        foreach ($all as $node) {
            $ns = $node['network_settings'] ?? [];
            $hasRaw = is_array($ns) && !empty($ns['raw_uri']);
            if ($hasRaw) {
                try { if ($node->delete()) { $deleted++; } } catch (\Exception $e) {}
            }
        }
        $status = "已删除 {$deleted} 条自定义节点";
        return redirect()->back()->with(['status' => $status, 'errors' => []]);
    }

    private function csvToArray(?string $csv): array
    { if (!$csv) return []; $arr = array_filter(array_map('trim', explode(',', $csv)), fn($v)=> $v !== ''); return array_values($arr); }

    private function parseLinkMeta(string $link): array
    {
        $res = ['protocol'=>null,'host'=>null,'port'=>null,'name'=>null];
        if (stripos($link, 'vmess://') === 0) {
            $payload = substr($link, 8); $decoded = base64_decode($payload, true);
            if ($decoded !== false) { $json = json_decode($decoded, true); if (is_array($json)) { $res['protocol']='vmess'; $res['host']=$json['add']??null; $res['port']=isset($json['port'])?(int)$json['port']:null; $res['name']=$json['ps']??null; return $res; } }
        }
        $parsed = @parse_url($link);
        if (is_array($parsed)) { $res['protocol']=$parsed['scheme']??null; $res['host']=$parsed['host']??null; $res['port']=isset($parsed['port'])?(int)$parsed['port']:null; if (isset($parsed['fragment'])) { $res['name']=urldecode($parsed['fragment']); } }
        return $res;
    }
}

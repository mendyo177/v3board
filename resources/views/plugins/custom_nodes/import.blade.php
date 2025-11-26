@extends('admin')
@section('content')
<div class="container" style="padding:24px 32px;">
  <h2 style="margin-bottom:16px;">自定义节点批量导入</h2>
  @if(session('status'))
    <div style="padding:8px 12px;background:#e6fffb;border:1px solid #87e8de;margin-bottom:12px;">{{ session('status') }}</div>
  @endif
  @if(session('errors'))
    @foreach(session('errors') as $err)
      <div style="padding:6px 10px;background:#fff1f0;border:1px solid #ffa39e;margin-bottom:6px;color:#cf1322;">{{ $err }}</div>
    @endforeach
  @endif
  <div style="margin-bottom:16px;">
    <strong>许可证状态：</strong>
    @if($license_valid)
      <span style="color:#389e0d;">已激活</span>
    @else
      <span style="color:#d4380d;">未激活（导入与删除操作将被拒绝）</span>
    @endif
  </div>
  <form method="POST" action="{{ url($secure_path . '/server/custom-nodes') }}" style="border:1px solid #f0f0f0;padding:16px;border-radius:4px;background:#fff;">
    @csrf
    <div style="display:flex;flex-wrap:wrap;gap:16px;">
      <div style="flex:1;min-width:240px;">
        <label>分组ID (逗号分隔)</label>
        <input name="group_id" class="form-control" placeholder="如: 4,5" />
      </div>
      <div style="flex:1;min-width:240px;">
        <label>路由ID (逗号分隔)</label>
        <input name="route_id" class="form-control" />
      </div>
      <div style="flex:1;min-width:240px;">
        <label>名称前缀</label>
        <input name="prefix" value="☁️-" class="form-control" />
      </div>
      <div style="flex:1;min-width:240px;">
        <label>倍率</label>
        <input name="rate" value="1" class="form-control" />
      </div>
      <div style="flex:1;min-width:240px;">
        <label>显示 (1/0)</label>
        <input name="show" value="1" class="form-control" />
      </div>
      <div style="flex:1;min-width:240px;">
        <label>起始排序 (默认6000)</label>
        <input name="sort" class="form-control" />
      </div>
      <div style="flex:1;min-width:240px;">
        <label>标签 (逗号分隔)</label>
        <input name="tags" class="form-control" />
      </div>
    </div>
    <div style="margin-top:16px;">
      <label>原始链接（每行一个 vmess:// 或其它）</label>
      <textarea name="raw" rows="10" style="width:100%;font-family:monospace;" required></textarea>
    </div>
    <div style="margin-top:16px;display:flex;gap:12px;">
      <button type="submit" class="btn btn-primary" @if(!$license_valid) disabled @endif>批量导入</button>
      <button type="button" class="btn btn-danger" onclick="if(confirm('确认删除所有自定义节点?')){ fetch('{{ url($secure_path . '/server/custom-nodes/delete-all') }}',{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload()); }" @if(!$license_valid) disabled @endif>删除全部</button>
    </div>
  </form>
  <hr />
  <h3>许可证激活</h3>
  <form method="POST" action="{{ url($secure_path . '/server/custom-nodes/activate') }}" style="display:flex;gap:8px;align-items:center;">
    @csrf
    <input name="license" placeholder="输入许可证" class="form-control" style="max-width:260px;" />
    <button class="btn btn-success" type="submit">激活</button>
  </form>
  <p style="margin-top:8px;font-size:12px;color:#888;">许可证由站点名称散列生成，请联系作者获取或自行生成。</p>
  <p style="margin-top:4px;font-size:12px;color:#888;">作者 Telegram：<a href="https://t.me/v2b_plus" target="_blank" rel="noopener noreferrer">https://t.me/v2b_plus</a></p>
</div>
@endsection

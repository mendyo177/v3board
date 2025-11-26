<img src="https://avatars.githubusercontent.com/u/56885001?s=200&v=4" alt="logo" width="130" height="130" align="right"/>

[![](https://img.shields.io/badge/TgChat-@UnOfficialV2board讨论-blue.svg)](https://t.me/unofficialV2board)

## 本分支支持的后端
 - [修改版V2bX](https://github.com/wyx2685/V2bX)
 - 最新版v2node


## 原版迁移步骤

按以下步骤进行面板代码文件迁移：

    git remote set-url origin https://github.com/mendyo177/v3board  
    git checkout master  
    ./update.sh  


按以下步骤配置缓存驱动为redis，然后刷新设置缓存，重启队列:

    sed -i 's/^CACHE_DRIVER=.*/CACHE_DRIVER=redis/' .env
    php artisan config:clear
    php artisan config:cache
    php artisan horizon:terminate

最后进入后台重新保存主题： 主题配置-选择default主题-主题设置-确定保存

# **V3Board**

- PHP7.3+
- Composer
- MySQL5.5+
- Redis
- Laravel
## 新增功能（简述）
- 自定义节点批量导入：后台支持批量导入原始节点链接，便于集中管理。
- 通用订阅：支持导出多种客户端格式的订阅。
- 本地验证码：本地生成图片/SVG 验证码，减少对外部服务依赖（面板可配置开启/关闭）。
- 后台界面增强：加入导入入口并优化在线状态显示。

## 升级提示
如需回退到原来的外部验证码服务，只需在面板中关闭验证码开关即可。

## Sponsors
Thanks to the open source project license provided by [Jetbrains](https://www.jetbrains.com/)

## How to Feedback
Follow the template in the issue to submit your question correctly, and we will have someone follow up with you.

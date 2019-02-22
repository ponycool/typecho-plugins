## Typecho 文章浏览量统计插件 PageViews v1.0.0
---
统计站点内容页面的总浏览量，所谓内容页面，包括文章页、独立页面和附件显示页

**使用方法**

1. 下载本插件，放在 usr/plugins/ 目录中
2. 登录管理后台，激活插件
3. 在显示的地方调用显示方法
```php
语法: PageViews_Plugin::showPageViews();
输出: '本站总访问量 XX 次'

语法: PageViews_Plugin::showPageViews('点击量','次');
输出: '点击量 XX 次'
```

**与我联系**

* 作者主页：http://blog.ponycool.com 
* Email: pony#ponycool.com(将#替换为@)

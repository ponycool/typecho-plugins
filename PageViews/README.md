## Typecho 文章浏览量统计插件 PageViews v1.2.0
---
统计站点内容页面的总浏览量，所谓内容页面，包括文章页、独立页面和附件显示页

**使用方法**

1. 下载本插件，放在 usr/plugins/ 目录中
2. 登录管理后台，激活插件
3. 在需要显示的地方调用显示方法

```php
语法: PageViews_Plugin::showPageViews();
输出: '本站总访问量 XX 次'
```

### Logs

- 1.2.0
    - 修改配置方式为后台插件配置
    - 适配PHP8
    - 新增人类易读模式
- 1.0.1
    - 增加起始浏览量
    - 简化逻辑

### Links

- Blog：https://mayanpeng.cn
- Email: pony#ponycool.com(将#替换为@)
- Github: https://github.com/PonyCool

# 获取真实IP -- 开发...

如果您的服务器位于反向代理后面，获取的IP为反向代理的IP。如果要获取客户端的真实IP，则必须将代理列入白名单。

**本插件暂时未完成开发不可用!!!!!!!**

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

**Links**

- Blog：http://blog.ponycool.com 
- Email: pony#ponycool.com(将#替换为@)
- Github: https://github.com/PonyCool

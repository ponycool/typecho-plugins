# Typecho文件上传插件Upload

### 介绍

用于将Typecho上传的附件保存到对象存储

插件使用说明

为保证文件上传及记录日志，请赋予以下目录写权限：
- `/var/webroot/blog/usr/uploads/`
- `/var/webroot/blog/usr/logs/`

定期查阅日志处理事件错误。开启SELinux的用户注意合理配置权限。
当文件成功上传到OSS，但保存到服务器失败时，总体进度会显示失败。在OSS中的文件不会自动删除，请根据错误日志自行处理。
旧版本Typecho存在无法上传大写扩展名文件的bug，请更新Typecho程序。
以下是本插件产生的错误日志，请定期查看并处理：
日志文件是 `/var/webroot/blog/usr/logs/upload_plugin/upload-error.log`

目前支持的对象存储：

- OSS

***

### 特性

1. 可以选择只保存到对象存储，或同时保存到对象存储和服务器，方便维护；
2. 实现文件上传时自动重命名；
3. 记录上传文件时的错误日志；
4. 可以自定义CDN地址、对象储存前缀、对象存储连接方式（内网、外网）；
5. 可以使用自定义样式。
6. 增加文件hash校验，重复文件只上传一次
7. 增加媒体管理，可查看历史上传的文件信息

## 使用方法

1. 下载本插件，放在 usr/plugins/ 目录中
2. 登录管理后台，激活插件
3. 后台配置插件信息

### Links

- Blog：https://mayanpeng.cn
- Email: pony#ponycool.com(将#替换为@)
- Github: https://github.com/PonyCool

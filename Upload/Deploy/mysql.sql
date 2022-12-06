create table typecho_media
(
    id             int auto_increment comment '自增长ID'
        primary key,
    type           varchar(20)              null comment '媒体类型：image、video、audio',
    file_name      varchar(50)              null comment '文件名',
    file_url       varchar(500)             null comment '文件路径',
    external_url   varchar(500)             null comment '外网地址',
    intranet_url   varchar(500)             null comment '内网地址',
    cdn_url        varchar(500)             null comment 'CDN地址',
    object_storage char(10)                 null comment '对象存储类型',
    size           int                      null comment '文件大小，单位byte',
    size_by_unit   varchar(50)              null comment '大小，单位MB',
    ext            char(10)                 null comment '文件扩展名',
    mime           varchar(20)              null comment 'MIME类型',
    width          int                      null comment '宽度：如果文件类型为image需要此属性，单位像素',
    height         int                      null comment '高度：如果文件类型为image需要此属性，单位像素',
    status         varchar(10) default '0'  null comment '文件状态：normal代表正常，froze代表被冻结，pass代表审核通过',
    md5            varchar(50)              null comment 'MD5',
    hash           varchar(1024)            null comment '文件hash值',
    hash_alg       varchar(10)              null comment 'HASH算法',
    server_replica bit                      null comment '服务器副本：0无，1有',
    created_at     datetime                 null comment '创建时间',
    updated_at     datetime                 null comment '更新时间',
    deleted_at     datetime                 null comment '删除时间',
    deleted        bit         default b'0' null comment '逻辑删除：0正常，1删除'
) comment '媒体';

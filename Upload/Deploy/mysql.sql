create table blog_media
(
    id           int auto_increment comment '自增长ID'
        primary key,
    type         varchar(20) null,
    file_name    varchar(50) null,
    file_url     varchar(500) null,
    size         int null,
    size_by_unit varchar(50) null,
    exts         char(10) null,
    width        int null,
    height       int null,
    status       varchar(10) null,
    hash         varchar(1024) null,
    hash_alg     varchar(10) null,
    created_at   datetime null comment '创建时间',
    updated_at   datetime null comment '更新时间',
    deleted_at   datetime null comment '删除时间',
    deleted      bit default 0 null comment '逻辑删除：0正常，1删除'
) comment '媒体';


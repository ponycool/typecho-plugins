create table typecho_ads
(
    id         int auto_increment comment '自增长ID'
        primary key,
    ad_name    varchar(50)      null comment '广告位名称',
    ad_desc    varchar(200)     null comment '广告位描述',
    ad_code    text             null comment '广告位代码',
    view_times int default 0    null comment '点击次数',
    sort_index int              null comment '排序索引',
    created_at datetime         null comment '创建时间',
    updated_at datetime         null comment '更新时间',
    deleted_at datetime         null comment '删除时间',
    deleted    bit default b'0' null comment '逻辑删除：0正常，1删除'
)
    comment '广告投放';


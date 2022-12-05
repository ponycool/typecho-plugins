#!/usr/bin/env bash

DIR_NAME=$0
if [ "${DIR_NAME:0:1}" = "/" ]; then
  CURR_DIR=$(dirname "$DIR_NAME")
else
  CURR_DIR="$(pwd)"/"$(dirname "$DIR_NAME")"
fi
. "$CURR_DIR"/message.sh

TARGET_PATH=$CURR_DIR/../
SOURCE_PATH=$CURR_DIR/../../blog/usr/plugins/
declare -a PLUGINS
PLUGINS[0]="Ads"
PLUGINS[1]="Cors"
PLUGINS[2]="GetRealIP"
PLUGINS[3]="Mourn"
PLUGINS[4]="PageViews"
PLUGINS[5]="RunTime"
PLUGINS[6]="TagsList"
PLUGINS[7]="Upload"

info "开始同步......"

if [ ! -d "$SOURCE_PATH" ]; then
  error "源目录不存在"
fi

for i in ${!PLUGINS[@]}; do
  if [ ! -d "$SOURCE_PATH${PLUGINS[$i]}" ]; then
    error "插件目录${PLUGINS[$i]}不存在"
  fi

  #  开始同步插件
  cp -a "$SOURCE_PATH${PLUGINS[$i]}" $TARGET_PATH
  if [ "$?" != 0 ]; then
    error "插件${PLUGINS[$i]}同步失败"
  else
    success "插件${PLUGINS[$i]}同步成功"
  fi
done

# 同步结果
if [ "$?" != 0 ]; then
  error "插件同步失败"
else
  success "所有同步完成"
fi

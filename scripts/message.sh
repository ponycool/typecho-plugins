#!/usr/bin/env bash

# 提示信息
function info() {
  echo -e "\033[32m提示信息：$1\033[0m"
}

# 成功信息
function success() {
  echo -e "\033[36m成功信息：$1\033[0m"
}

# 错误信息
function error() {
  echo -e "\033[31m错误信息：$1\033[0m"
  exit
}

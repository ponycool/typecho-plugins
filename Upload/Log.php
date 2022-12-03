<?php

namespace Typecho\Plugin\Upload;

use Typecho\Common;
use Exception;

/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/28
 * Time: 11:04 AM
 */
class Log
{
    protected string $logFile;
    protected string $logContent;
    protected string $logColor;
    protected bool $writable;

    public function __construct()
    {
        $logFile = sprintf('%s%s' . 'upload_plugin%s%s-error.log',
            UPLOAD_LOG_ROOT,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            UPLOAD_LOG_PREFIX
        );
        $this->setLogFile($logFile);

        $this->initLog();
    }

    /**
     * @return string
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }

    /**
     * @param string $logFile
     */
    public function setLogFile(string $logFile): void
    {
        $this->logFile = $logFile;
    }

    /**
     * @return string
     */
    public function getLogContent(): string
    {
        return $this->logContent;
    }

    /**
     * @param string $logContent
     */
    public function setLogContent(string $logContent): void
    {
        $this->logContent = $logContent;
    }

    /**
     * @return string
     */
    public function getLogColor(): string
    {
        return $this->logColor;
    }

    /**
     * @param string $logColor
     */
    public function setLogColor(string $logColor): void
    {
        $this->logColor = $logColor;
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * @param bool $writable
     */
    public function setWritable(bool $writable): void
    {
        $this->writable = $writable;
    }


    /**
     * 初始化日志
     * @return void
     */
    private function initLog(): void
    {
        if (is_writable($this->getLogFile())) {
            $this->setLogContent('恭喜！暂无错误日志产生，请继续保持维护～');
            $this->setLogColor('#009900');

            if (!file_exists($this->getLogFile())) {
                fopen($this->getLogFile(), 'w');
                if (!file_exists($this->getLogFile())) {
                    $log_content = '无法创建日志文件，请检查权限设置！！！开启SELinux的用户注意合理配置权限！';
                    $this->setLogContent($log_content);
                    $this->setLogColor('#f00000');
                }
            } else {
                try {
                    $content = file_get_contents($this->getLogFile());
                    if ($content) {
                        $this->setLogContent($content);
                        $this->setLogColor('#dd0000');
                    }
                } catch (Exception) {
                    $this->setLogContent('注意！无法读取日志文件，请检查文件状态！');
                    $this->setLogColor('#f00000');
                }
            }
        } else {
            $logContent = '！！！注意！！！ ' . PHP_EOL;
            $logContent .= '当前网站上传目录无写入权限，无法记录日志！' . PHP_EOL;
            $logContent .= '请给路径 ' . $this->getLogFile() . ' 赋予写入权限。开启SELinux的用户注意合理配置权限。';
            $this->setLogContent($logContent);
            $this->setLogColor('#f00000');
        }
    }

    /**
     * 日志
     * @param string $message
     * @return void
     */
    public static function message(string $message): void
    {
        if (!Common::isAppEngine()) {
            $time = '时间：' . date('Y-m-d h:i:sA');
            $log = new Log();
            $logfile = $log->getLogFile();
            $logDir = dirname($logfile);
            if (!is_dir($logDir)) {
                if (is_writeable(UPLOAD_LOG_ROOT)) {
                    Handle::makeDir($logDir);
                }
            }
            if (is_writeable($logDir)) {
                $message = $time . ' ' . $message . PHP_EOL;
                error_log($message, 3, $logfile);
            }
        }
    }

}
<?php

namespace app\lib;

class EnvOperation
{

    private $env;
    private $exampleEnv;
    public function __construct($exampleEnv)
    {
        $this->exampleEnv = $exampleEnv;
    }

    /**
     * @return mixed
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @param mixed $env
     */
    public function setEnv($env): void
    {
        $this->env = $env;
    }

    public function purify($env = [])
    {
        preg_match_all('#{{(.+?)}}#', $this->env, $matches, PREG_SET_ORDER);
        foreach ($matches as $v) {
            $list = explode(':', $v[1]);
            $value = isset($env[$list[0]]) ? $env[$list[0]] : '';
            $defaultValue = isset($list[1]) ? $list[1] : '';
            $type = isset($list[2]) ? $list[2] : '';
            if ($type === 'bool') {
                $value = var_export(boolval($value), 1);
            }
            if (empty($value)) {
                $value = $defaultValue;
            }
            $this->env = preg_replace('#' . $v[0] . '#', $value, $this->env);
        }
    }

    public function set($key, $newValue)
    {
        if (is_null($this->env)) {
            $this->env = preg_replace('#{{' . $key . '}}#', $newValue, $this->exampleEnv);
        } else {
            $this->env = preg_replace('#{{' . $key . '}}#', $newValue, $this->env);
        }
    }

    public function save()
    {
        $envPath = app()->getRootPath() . '.env';
        $rootPath = app()->getRootPath();
        
        if (!is_writable($rootPath) && !file_exists($envPath)) {
            throw new \Exception('项目根目录没有写入权限，无法创建 .env 文件');
        }
        
        if (file_exists($envPath) && !is_writable($envPath)) {
            throw new \Exception('.env 文件没有写入权限');
        }
        
        if (empty($this->env)) {
            throw new \Exception('环境配置内容为空');
        }
        
        $result = @file_put_contents($envPath, $this->env);
        if ($result === false) {
            throw new \Exception('写入 .env 文件失败，请检查文件权限');
        }
        
        return $result;
    }

}

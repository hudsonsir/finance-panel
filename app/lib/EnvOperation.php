<?php

namespace app\controller;


use app\lib\EnvOperation;
use app\lib\ExecSQL;
use PDO;
use think\Exception;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Validate;
use think\facade\View;
use think\helper\Str;


class Install extends Base
{

    public function __destruct()
    {
        Cache::clear();
        reset_opcache();
    }

    public function initialize()
    {
        if (file_exists(app()->getRootPath() . 'install.lock')) {
            exit('你已安装成功，需要重新安装请删除 install.lock 文件');
        }
        Cache::clear();
        reset_opcache();
    }

    public function index()
    {
        $rootPath = app()->getRootPath();
        $envExamplePath = $rootPath . '.env.example';
        
        $requirements = [
            'PHP >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'PDO_MySQL' => extension_loaded("pdo_mysql"),
            'CURL' => extension_loaded("curl"),
            'ZipArchive' => class_exists("ZipArchive"),
            'runtime写入权限' => is_writable(app()->getRuntimePath()),
            '根目录写入权限' => is_writable($rootPath) || (file_exists($rootPath . '.env') && is_writable($rootPath . '.env')),
            '.env.example文件存在' => file_exists($envExamplePath),
        ];
        reset_opcache();
        $step = Request::param('step');
        View::assign([
            'step' => $step,
            'requirements' => $requirements,
        ]);
        return view('../view/install/index.html');
    }

    public function database()
    {
        $params = Request::param();
        $rules = [
            'hostname' => 'require',
            'hostport' => 'require|integer',
            'username' => 'require',
            'password' => 'require',
            'database' => 'require',
        ];
        $validate = Validate::rule($rules);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        $hostname = preg_replace('/[^a-zA-Z0-9._-]/', '', $params['hostname']);
        $database = preg_replace('/[^a-zA-Z0-9_]/', '', $params['database']);
        $hostport = intval($params['hostport']);
        if($hostport < 1 || $hostport > 65535) {
            return msg('error', '端口范围不正确');
        }
        
        $dsn = 'mysql:host=' . $hostname . ';dbname=' . $database . ';port=' . $hostport . ';charset=utf8';
        try {
            new PDO($dsn, $params['username'], $params['password']);
        } catch (\Exception $e) {
            return msg('error', '数据库连接失败，请检查配置信息');
        }
        try {
            $envExamplePath = app()->getRootPath() . '.env.example';
            if (!file_exists($envExamplePath)) {
                return msg('error', '.env.example 文件不存在，请确保文件已上传');
            }
            
            if (!is_readable($envExamplePath)) {
                return msg('error', '.env.example 文件不可读，请检查文件权限');
            }
            
            $envFile = file_get_contents($envExamplePath);
            if ($envFile === false) {
                return msg('error', '读取 .env.example 文件失败');
            }
            
            $envOperation = new EnvOperation($envFile);
            foreach (array_keys($rules) as $value) {
                $envOperation->set(mb_strtoupper($value), $params[$value]);
            }
            
            $envOperation->save();
        } catch (\Exception $e) {
            \think\facade\Log::error('保存环境配置失败: ' . $e->getMessage());
            
            $errorMsg = $e->getMessage();
            if (strpos($errorMsg, '权限') !== false || strpos($errorMsg, 'permission') !== false) {
                return msg('error', $errorMsg . '。请设置项目根目录或 .env 文件的写入权限（建议 755 或 777）');
            }
            return msg('error', $errorMsg);
        }
        return msg();
    }

    public function init_data()
    {
        try {

            $filename = app()->getRootPath() . 'install.sql';
            if (!is_file($filename)) {
                throw new Exception('数据库 install.sql 文件不存在');
            }
            $install_sql = file($filename);
            $execSQL = new ExecSQL();
            $install_sql = $execSQL->purify($install_sql);
            foreach ($install_sql as $sql) {
                $execSQL->exec($sql);
                if (!empty($execSQL->getErrors())) {
                    throw new Exception($execSQL->getErrors()[0]);
                }
            }
            
            if (file_exists(app()->getRootPath() . '.env')) {
                app()->loadEnv();
            }
            \think\facade\Db::setConfig(['prefix' => 'panel_'], 'mysql');
            
            $defaultTemplate = \app\service\EmailService::getDefaultTemplate();
            $emailChannel = \think\facade\Db::name('notification')
                ->where('channel_type', 'email')
                ->find();
            if ($emailChannel) {
                $config = json_decode($emailChannel['config'], true);
                $config['template'] = $defaultTemplate;
                \think\facade\Db::name('notification')
                    ->where('id', $emailChannel['id'])
                    ->update([
                        'config' => json_encode($config, JSON_UNESCAPED_UNICODE),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }
        } catch (\Exception $e) {
            \think\facade\Log::error('数据库初始化失败: ' . $e->getMessage());
            return msg('error', '数据库初始化失败，请检查配置和权限');
        }
        return msg();
    }

    public function admin()
    {
        $params = Request::param();
        $rules = [
            'username|用户名' => 'require',
            'password|密码' => 'require',
        ];
        $validate = Validate::rule($rules);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }
        config_set("admin_username", $params['username']);
        config_set("admin_password", password_hash($params['password'], PASSWORD_DEFAULT));
        config_set("syskey", Str::random(16));
        file_put_contents(app()->getRootPath() . 'install.lock', format_date());
        return msg();
    }

}

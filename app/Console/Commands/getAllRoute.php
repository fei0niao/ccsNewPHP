<?php

namespace App\Console\Commands;

use App\Http\Model\AdminApi;
use Illuminate\Console\Command;

class getAllRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbInsert:router';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '写入所有router进数据库';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $app = app();
        $routes = $app->routes->getRoutes();
        foreach ($routes as $k => $v){
            if(!preg_match('/telescope/',$v->uri)){
                $data =[
                    'api_route' => $v->uri,
                    'name'      => "",
                    'method'    => implode($v->methods,','),
                    'remark'    => "",
                    'status'    => 1
                ];
                AdminApi::query()->updateOrCreate(['api_route' => $data['api_route']],$data);
            }
        }
    }
}

<?php

if (! function_exists('upload')) {
    /**
     * 上传文件函数
     *
     * @param $name             表单的name名
     * @param string $path      上传的路径 相对于public目录
     * @param bool $childPath   是否根据日期生成子目录
     * @return array            上传的状态
     */
    function upload($name, $path = 'uploads', $childPath = true){
        // 判断请求中是否包含name=file的上传文件
        if (!request()->hasFile($name)) {
            $data=[
                'status_code' => 501,
                'message' => '上传文件为空'
            ];
            return $data;
        }
        $file = request()->file($name);

        // 判断是否多文件上传
        if (!is_array($file)) {
            $file = [$file];
        }
        // 先去除两边空格
        $path = trim($path, '/');

        // 判断是否需要生成日期子目录
        $path = $childPath ? $path.'/'.date('Ymd') : $path;

        // 获取目录的绝对路径
        $publicPath = public_path($path.'/');

        // 如果目录不存在；先创建目录
        is_dir($publicPath) || mkdir($publicPath, 0755, true);

        // 上传成功的文件
        $success = [];

        // 循环上传
        foreach ($file as $k => $v) {
            //判断文件上传过程中是否出错
            if (! $v->isValid()) {
                $data=[
                    'status_code' => 500,
                    'message' => '文件上传出错'
                ];
                return $data;
            }
            // 获取上传的文件名
            $oldName = $v->getClientOriginalName();
            // 组合新的文件名
            $newName = uniqid().'.'.$v->getClientOriginalExtension();
            // 判断上传是否失败
            if (!$v->move($publicPath, $newName)) {
                $data=[
                    'status_code' => 500,
                    'message' => '保存文件失败'
                ];
                return $data;
            } else {
                $success[] = [
                    'name' => $oldName,
                    'path' => '/'.$path.'/'.$newName
                ];
            }
        }
        //上传成功
        $data=[
            'status_code' => 200,
            'message' => '上传成功',
            'data' => $success
        ];
        return $data;
    }
}
<?php
namespace app\common\controller;


class Task {

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(\HttpRequest $req, \HttpResponse $res) {
        $this->request = $req;
        $this->response = $res;
    }


}
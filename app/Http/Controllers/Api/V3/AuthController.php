<?php

namespace App\Http\Controllers\Api\V3;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Swagger\Annotations as SWG;

class AuthController extends BaseController
{
    /**
     * @SWG\Post(
     *   path="/authorizations",
     *   summary="创建一个token (create a token)",
     *   description="创建一个token (create a token)",
     *   tags={"Auth"},
     *   @SWG\Parameter(
     *     type="string",
     *     description="邮箱",
     *     name="email",
     *     required=true,
     *     in="formData"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     type="string",
     *     description="密码",
     *     required=true,
     *     in="formData"
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="Token created",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(
     *         property="token",
     *         type="string",
     *         description="jwt的token值",
     *         default="",
     *         example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbHVtZW4tYXBpLWRlbW8uZGV2L2FwaS9hdXRob3JpemF0aW9ucyIsImlhdCI6MTQ4Mzk3NTY5MywiZXhwIjoxNDg5MTU5NjkzLCJuYmYiOjE0ODM5NzU2OTMsImp0aSI6ImViNzAwZDM1MGIxNzM5Y2E5ZjhhNDk4NGMzODcxMWZjIiwic3ViIjo1M30.hdny6T031vVmyWlmnd2aUr4IVM9rm2Wchxg5RX_SDpM"
     *       ),
     *       @SWG\Property(
     *         property="expired_at",
     *         type="string",
     *         description="过期时间",
     *         default="",
     *         example="2017-03-10 15:28:13"
     *       ),
     *       @SWG\Property(
     *         property="refresh_expired_at",
     *         type="string",
     *         description="刷新token的过期时间",
     *         default="",
     *         example="2017-01-23 15:28:13"
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response=404,
     *     description="User Not Found",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(
     *         property="error",
     *         description="错误信息",
     *         type="string",
     *         default="UserNotFound"
     *       )
     *     )
     *
     *   )
     * )
     *
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $credentials = $request->only('email', 'password');

        // 验证失败返回403
        if (! $token = \Auth::attempt($credentials)) {
            $this->response->errorForbidden(trans('auth.incorrect'));
        }

        $result = [
            'token' => $token,
            'expired_at' => Carbon::now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'refresh_expired_at' => Carbon::now()->addMinutes(config('jwt.refresh_ttl'))->toDateTimeString(),
        ];

        return $this->response->array($result)->setStatusCode(201);
    }

    /**
     * @SWG\Put(
     *   path="/authorizations/current",
     *   summary="刷新token(refresh token)",
     *   description="刷新token(refresh token)",
     *   tags={"Auth"},
     *   @SWG\Parameter(
     *     type="string",
     *     description="用户旧的jwt-token, value以Bearer开头",
     *     name="Authorization",
     *     required=true,
     *     in="header",
     *     default="Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY"
     *   ),
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Token updated",
     *     examples={
     *       "application/json":{
     *         "token"="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbHVtZW4tYXBpLWRlbW8uZGV2L2FwaS9hdXRob3JpemF0aW9ucyIsImlhdCI6MTQ4Mzk3NTY5MywiZXhwIjoxNDg5MTU5NjkzLCJuYmYiOjE0ODM5NzU2OTMsImp0aSI6ImViNzAwZDM1MGIxNzM5Y2E5ZjhhNDk4NGMzODcxMWZjIiwic3ViIjo1M30.hdny6T031vVmyWlmnd2aUr4IVM9rm2Wchxg5RX_SDpM",
     *         "expired_at"="2017-03-10 15:28:13",
     *         "refresh_expired_at"="2017-01-23 15:28:13"
     *       }
     *     }
     *   )
     * )
     *
     */
    public function update()
    {
        // check token
        // same with \Auth::requireToken()->checkOrFail();
        \Auth::getPayload();

        $result = [
            'token' => \Auth::refresh(),
            'expired_at' => Carbon::now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'refresh_expired_at' => Carbon::now()->addMinutes(config('jwt.refresh_ttl'))->toDateTimeString(),
        ];

        return $this->response->array($result);
    }

    /**
     * @api {delete} /authorizations/current 删除当前token (delete current token)
     * @apiDescription 删除当前token (delete current token)
     * @apiGroup Auth
     * @apiPermission jwt
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 204 No Content
     */
    public function destroy()
    {
        \Auth::logout();

        return $this->response->noContent();
    }
}

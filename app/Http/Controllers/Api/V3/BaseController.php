<?php

namespace App\Http\Controllers\Api\V3;

use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Dingo\Api\Exception\ValidationHttpException;
use Swagger\Annotations as SWG;

/**
 * @SWG\Swagger(
 *     basePath="/api",
 *     host="localhost:8800",
 *     produces={"application/json"},
 *     schemes={"http"},
 *     @SWG\Info(
 *         version="3.0.0",
 *         title="Cityzine API",
 *         description="Cityzine API Documentation",
 *         @SWG\Contact(name="service@citytalk.tw", url="https://www.citytalk.tw/zine/"),
 *     ),
 *     @SWG\Definition(
 *         definition="Error",
 *         required={"code", "message"},
 *         @SWG\Property(
 *             property="code",
 *             type="integer",
 *             format="int32"
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *     ),
 *     @SWG\SecurityScheme(
 *       securityDefinition="jwt",
 *       type="apiKey",
 *       in="header",
 *       name="Authorization"
 *     )
 * )
 */
class BaseController extends Controller
{
    // 接口帮助调用
    use Helpers;

    // 返回错误的请求
    protected function errorBadRequest($validator)
    {
        // github like error messages
        // if you don't like this you can use code bellow
        //
        //throw new ValidationHttpException($validator->errors());

        $result = [];
        $messages = $validator->errors()->toArray();

        if ($messages) {
            foreach ($messages as $field => $errors) {
                foreach ($errors as $error) {
                    $result[] = [
                        'field' => $field,
                        'code' => $error,
                    ];
                }
            }
        }

        throw new ValidationHttpException($result);
    }
}

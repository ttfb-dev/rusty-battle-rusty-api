<?php


namespace App\Http\Middleware;

use Closure;

class IsMD5Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $md5_string = $request->route()[2]['image_name'];

        if ($this->isValidMd5($md5_string)) {
            return $next($request);
        }

        throw new \Exception('Некорректная ссылка на картинку');

    }

    private function isValidMd5($md5 ='')
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }
}

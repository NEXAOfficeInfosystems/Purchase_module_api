<?php
  
// namespace App\Filters;
  
// use CodeIgniter\Filters\FilterInterface;
// use CodeIgniter\HTTP\RequestInterface;
// use CodeIgniter\HTTP\ResponseInterface;
// use Firebase\JWT\JWT;
// use Firebase\JWT\Key;
// use Config\Services;
  
// class AuthFilter implements FilterInterface
// {
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
  
// public function before(RequestInterface $request, $arguments = null)
// {
//     // Skip auth check for GET requests
//     if (strtoupper($request->getMethod()) ===  'OPTIONS') {
//         return;
//     }

//     $config = config('Jwt');
//     $key = $config->authKey;
//     $header = $request->getServer('HTTP_AUTHORIZATION');
//     $userAuthKey = $request->getServer('HTTP_AUTHKEY');
//     $authKey = getenv('AUTHKEY');
//     if (!$header || $userAuthKey != $authKey) {
//         return Services::response()
//             ->setJSON(['msg' => 'Unauthorized'])
//             ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
//     }
//     $token = explode(' ', $header)[1];
//     try {
//         $decodedToken = JWT::decode($token, new Key($key, $config->method));
//     } catch (\Throwable $th) {
//         return Services::response()
//             ->setJSON(['msg' => 'Unauthorized'])
//             ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
//     }
//     $request->user = $decodedToken;
//     return $request;
// }
  
    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    // public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    // {
    //     //
    // }
// }


namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\Services;
use Exception;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Skip auth check for OPTIONS requests (CORS preflight)
        $excludedRoutes = [
            'api/generateToken',
            'api/getsetting',
            'api/maintenance',
            'auth/login',
            'auth/register'
        ];
         $uri = $request->getUri()->getPath();
         foreach ($excludedRoutes as $route) {
        if (strpos($uri, $route) !== false) {
            return $request; // No JWT required
        }
    }

        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return;
        }

        $config = config('Jwt');
        $key = $config->authKey;
        $authKey = getenv('AUTHKEY');
        $userAuthKey = $request->getServer('AUTHKEY');

        // Check AUTHKEY header (if required)
        if ($userAuthKey != $authKey) {
            return $this->unauthorized('Invalid or missing API key');
        }

        // Check Authorization header exists
        $header = $request->getServer('HTTP_AUTHORIZATION');
        if (empty($header)) {
            return $this->unauthorized('Authorization header missing');
        }

        // Extract Bearer token
        if (!preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $this->unauthorized('Invalid token format (expected: Bearer <token>)');
        }

        $token = $matches[1];

        try {
            $decodedToken = JWT::decode($token, new Key($key, $config->method));

            // Manually check expiration (if 'exp' claim exists)
            if (isset($decodedToken->exp) && $decodedToken->exp < time()) {
                return $this->unauthorized('Token expired');
            }

            // Attach decoded user data to the request
            $request->user = $decodedToken;

        } catch (Exception $e) {
            return $this->unauthorized('Invalid token: ' . $e->getMessage());
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }

    /**
     * Helper method to return a consistent unauthorized response.
     */
    private function unauthorized(string $message)
    {
        return Services::response()
            ->setJSON(['success' => false, 'error' => $message])
            ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
    }
}
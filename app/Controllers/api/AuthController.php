<?php
namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\api\TempCustomersModel;
use Firebase\JWT\JWT;
use CodeIgniter\RESTful\ResourceController;

use App\Models\Customer\UsersModel;

use App\Models\Customer\CustomersModel;
use Firebase\JWT\Key;

class AuthController extends ResourceController
{
    use ResponseTrait;


    public function register()
    {
        $requestJson = $this->request->getJSON();
        $email = $requestJson->email;
        if ($this->isEmailRegistered($email)) {
            $message = [
                'message' => 'Email is already registered',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
        }
        $name = isset($requestJson->name) ? $requestJson->name : null;
        $phone = isset($requestJson->phone) ? $requestJson->phone : null;
        $password = isset($requestJson->password) ? $requestJson->password : null;
        $email = isset($requestJson->email) ? $requestJson->email : null;
        $user_type = 'customer';

        $UsersModels = new UsersModel();
        $userId = $UsersModels->insert([
            'name' => $name,
            'phone' => $phone,
            'password' => password_hash($password,PASSWORD_BCRYPT),
            'email' => $email,
            'user_type' => $user_type,
        ]);
        $token = $this->generateToken($userId, $email);
        if ($token) {

            $data = [
                'userId' => $userId,
                'token' => $token
            ];

            $message = [
                'message' => 'Registration successfull',
                'status' => 200
            ];

            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $data]);
        } else {

            $message = [
                'message' => 'Something when wrong please try again',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);

        }


    }   //////////////// ========= END OF REGISTER FUNCTION =============
public function generateGuestToken()
    {
        $config = config('Jwt');
        $key = $config->authKey;
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // Token valid for 1 hour
        $data = [
           
            'iat' => $issuedAt,
            'exp' => $expirationTime
        ];
        $token12 =JWT::encode($data, $key, $config->method);
         
       
            $message = [
                'message' => 'Login successfull!',
                'status' => 200
            ];
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $token12]);
    } 
    protected function generateToken($userId, $username)
    {
        $config = config('Jwt');
        $key = $config->authKey;
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // Token valid for 1 hour
        $data = [
            'user_id' => $userId,
            'email' => $username,
            'iat' => $issuedAt,
            'exp' => $expirationTime
        ];
        return JWT::encode($data, $key, $config->method);
    }  ///////// /=======  END OF GENERATETOEKN ==============


    public function login()
    {    
        $json = $this->request->getJSON();
        $model = new UsersModel();
        $con = [
            'email'=> $json->email
        ];
        $user = $model->where($con)->first();

if (!$user) {
            $message = [
                'message' => 'Invalid Email',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
        }
        // Check if user is active and of type customer
if ($user)        {
        if ($user['is_active']==0 || $user['user_type'] != 'customer') {
            $message = [
                'message' => 'User is Inactive',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);

        }
        }
        try {
           if(!password_verify(trim($json->password), trim($user['password'])) ) {
            // print_r(password_hash($json->password,PASSWORD_BCRYPT));
            // print_r($user['password']);
                 $message = [
                'message' => 'Check your password',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
            }
           if ( $user['user_type'] == 'customer' ) {
           
            $token = $this->generateToken($user['id'], $user['email']);
            $response_data=['token' => $token,'email' => $user['email'],"user_id"=>$user['id']];


            $data = [
                'user_id' => $user['id'],
                'user_token' => $token
            ];
            
            $model->set(['token' => $token])->where($con)->update();
            $message = [
                'message' => 'Login successfull!',
                'status' => 200
            ];
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $data]);
        } else {
            $message = [
                'message' => 'Invalid User!',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
            // return $this->fail(['messageobject' => $message]);
        }
        } catch (\Throwable $th) {
            throw $th;
        }
        
        
    }

    private function isEmailRegistered($email)
    {
        $userModels = new UsersModel();

        $existingUser = $userModels->where('Email', $email)->first();

        return $existingUser !== null;
    }   //////////////// =============== END OF ISEMAILREGISTRED ===========

    //////////////////////////////// LOGIN AND  REGISTERTION ///////////////////////

    // Helper method to send a verification email
    private function sendVerificationEmail($emails, $verificationToken)

    {

$json = $this->request->getJSON();

        $verificationLink = "http://localhost:4200/auth/emailverified?token=$verificationToken";
        $email = \Config\Services::email();
        $randomPassword = substr(str_shuffle('0123456789'), 0, 4);
$newPassword = "Plant@$randomPassword";

             $email->setFrom('officeinfosystems2024@gmail.com', 'Planet Nursery');
            $email->setTo($json->email);
            $email->setSubject('Your new password');
            $email->setMessage("Your temporary passoword : $newPassword");
        if ($email->send()) {
            return 1;
        } else {
            return 0;
        }
    }

   public function sendOtptomail(){
// Generate a random 5-digit OTP
$json = $this->request->getJSON();
    $otp = rand(10000, 99999);


    // Send OTP to email
    $email = \Config\Services::email();
      $email->initialize([
              'protocol' => 'smtp',
                'SMTPHost' => 'smtp.gmail.com',
                'SMTPPort' => 587,
                'SMTPAuth' => true,
                'SMTPUser' => 'officeinfosystems2024@gmail.com',
                'SMTPPass' => 'msiz gltz miut vtcr',
                'mailType' => 'html',
                'SMTPCrypto' => 'tls',
                'newline' => "\r\n"
            ]);
//    $randomPassword = substr(str_shuffle('0123456789'), 0, 5);
// $newPassword = "Plant@$randomPassword";

             $email->setFrom('officeinfosystems2024@gmail.com', 'Planet Nursery');
            $email->setTo($json->email);
            $email->setSubject('Verification OTP');
    $email->setMessage("Your OTP is: $otp.");
           
    if ($email->send()) {
     
        $db = \Config\Database::connect();
        $builder = $db->query("INSERT INTO otp (email, otp, created_at) VALUES (?, ?, NOW())", [$json->email,  $otp]);
       return $this->response->setStatusCode(200)->setJSON([
            'messageobject' => [
                'status' => 200,
                'message' => 'OTP sent successfully to your email.'
            ],
            
        ]);
       
    } else {
      return $this->response->setStatusCode(400)->setJSON([
            'messageobject' => [
                'status' => 400,
                'message' => 'Failed to send OTP. Please try again later.'
            ],
            
        ]);
    }
    }


public function verifyOtp()
{
    $json = $this->request->getJSON();
    $email = $json->email ?? null;
    $otp = $json->otp ?? null;

    if (!$email || !$otp) {
        $message = [
        'message' => 'Invalid email or OTP',
        'status' => 400
    ];
        return $this->response->setStatusCode(400)->setJSON([
            'messageobject' => $message,
            
        ]);
    }

    $db = \Config\Database::connect();
    $builder = $db->query(
        "SELECT * FROM otp WHERE email = ? AND otp = ? ORDER BY created_at DESC LIMIT 1",
        [$email, $otp]
    );
    $result = $builder->getRow();

    if ($result) {
        $createdAt = strtotime($result->created_at);
        $now = time();
      
        if (($now - $createdAt) > 3600) {
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => [
                'status' => 400,
                'message' => 'OTP has expired.'
            ]]);
        }

        return $this->response->setStatusCode(200)->setJSON(['messageobject' => [
            'status' => 200,
            'message' => 'OTP verified successfully.'
        ]]);
    } else {
       
        return $this->response->setStatusCode(400)->setJSON([
           'messageobject' => [
                'status' => 400,
                'message' => 'Invalid email or OTP'
            ]
        ]);
    }
}
    public function verifyEmail()
    {
        $token = $this->request->getGet('token');

        // Find the user with the given verification token

        $tempUserModel = new TempCustomersModel();
        $userModel = new CustomersModel();

        $tempuser = $tempUserModel->where('VerificationToken', $token)->first();

        if (!$tempuser) {
            return $this->failNotFound('Invalid verification token');
        }

        // Update the user's status to verified
        $tempUserModel->update($tempuser['Id'], ['IsVarified' => 1, 'VerificationToken' => null]);

        $tempUserData = $tempUserModel->find($tempuser['Id']);

        $customerData = [
            'Id' => $tempUserData['Id'],
            'CustomerName' => $tempUserData['CustomerName'],
            'ContactPerson' => $tempUserData['ContactPerson'],
            'Email' => $tempUserData['Email'],
            'Fax' => $tempUserData['Fax'],
            'MobileNo' => $tempUserData['MobileNo'],
            'PhoneNo' => $tempUserData['PhoneNo'],
            'Website' => $tempUserData['Website'],
            'Description' => $tempUserData['Description'],
            'Url' => $tempUserData['Url'],
            'IsVarified' => $tempUserData['IsVarified'],
            'IsUnsubscribe' => $tempUserData['IsUnsubscribe'] != null ? $tempUserData['IsUnsubscribe'] : 0,
            'CustomerProfile' => $tempUserData['CustomerProfile'],
            'Address' => $tempUserData['Address'],
            'CountryName' => $tempUserData['CountryName'],
            'CityName' => $tempUserData['CityName'],
            'CountryId' => $tempUserData['CountryId'],
            'CityId' => $tempUserData['CityId'],
            'IsWalkIn' => $tempUserData['IsWalkIn'] != null ? $tempUserData['IsWalkIn'] : 0,
            'CreatedDate' => $tempUserData['CreatedDate'] != null ? $tempUserData['CreatedDate'] : date('Y-m-d H:i:s'),
            'CreatedBy' => $tempUserData['CreatedBy'],
            'ModifiedDate' => $tempUserData['ModifiedDate'] != null ? $tempUserData['ModifiedDate'] : date('Y-m-d H:i:s'),
            'ModifiedBy' => $tempUserData['ModifiedBy'],
            'DeletedDate' => $tempUserData['DeletedDate'],
            'DeletedBy' => $tempUserData['DeletedBy'],
            'IsDeleted' => $tempUserData['IsDeleted'] != null ? $tempUserData['IsDeleted'] : 0,
            'Password' => $tempUserData['Password']
        ];

        $customerChk = $userModel->where('Id', $tempUserData['Id'])->first();

        if ($customerChk) {
            return $this->failNotFound('Invalid verification token');
        } else {
            $customer_Id = $userModel->insert($customerData);
            $token = $this->generateToken($customer_Id, $tempUserData['Email']);
            return $this->respond(['status' => 'success', 'message' => 'Email verification successful', 'token' => $token, 'Email' => $tempUserData['Email']]);
        }

    }
    
    
    
    //----------------------Validate Token----------------------------
    
    
    
    
    public function validateToken() {
    // Get the raw input to avoid automatic parsing issues
    $rawInput = $this->request->getBody();
    $json = json_decode($rawInput);
    
    // Validate input
    if (!$json || !isset($json->token)) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Invalid request format. Token is required.'
        ], 400);
    }

    $token = $json->token;
    
    // Basic token format validation
    if (!is_string($token) || empty(trim($token))) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Invalid token format'
        ], 400);
    }




    $config = config('Jwt');
    $key = $config->authKey;
    
    try { 
        $decodedToken = JWT::decode($token, new Key($key, $config->method));
       
        // Additional security checks
        if (!isset($decodedToken->user_id)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Token payload is invalid'
            ], 401);
        }
        
        // Check if token is blacklisted (if you implement token invalidation)
        if ($this->isTokenBlacklisted($token)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Token has been invalidated'
            ], 401);
        }
        
        // Check if user still exists
        // $this->load->model('user_model');
        $userModel = new UsersModel();
        if (!$userModel->find($decodedToken->user_id)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'User account no longer exists'
            ], 401);
        }
        
        // Return the decoded payload if needed by client
        return $this->respond([
            'status' => 'success',
            'message' => 'Token is valid',
            'data' => [
                'user_id' => $decodedToken->user_id,
                'expires_at' => $decodedToken->exp ?? null
            ]
        ]);
        
    } catch (\Firebase\JWT\ExpiredException $e) {
        log_message('error', 'Expired token: ' . $e->getMessage());
        return $this->respond([
            'status' => 'error',
            'message' => 'Token has expired',
            'code' => 'token_expired'
        ], 401);
    } catch (\Firebase\JWT\SignatureInvalidException $e) {
        log_message('error', 'Invalid token signature: ' . $e->getMessage());
        return $this->respond([
            'status' => 'error',
            'message' => 'Token signature is invalid',
            'code' => 'invalid_signature'
        ], 401);
    } catch (\DomainException $e) {
        log_message('error', 'Domain exception: ' . $e->getMessage());
        return $this->respond([
            'status' => 'error',
            'message' => 'Malformed token',
            'code' => 'malformed_token'
        ], 401);
    } catch (\Throwable $th) {
        log_message('error', 'Token validation error: ' . $th->getMessage());
        return $this->respond([
            'status' => 'error',
            'message' => 'Token validation failed',
            'code' => 'validation_failed'
        ], 401);
    }
}








//-------------------------------Validate token end----------------------------------//






/**
 * Check if token is blacklisted (optional)
 */
private function isTokenBlacklisted($token) {
    // Implement your token blacklist logic here
    // For example, check in database or cache
    return false; // Default to false if not implementing blacklist
}


    public function isEmailvalidation()
    {
        $email = $this->request->getGet('Email');
        $userModel = new CustomersModel();
        $existingUser = $userModel->where('Email', $email)->first();
        if ($existingUser !== null) {
            return $this->respond(['Email' => $existingUser['Email']]);

        } else {
            return $this->response->setStatusCode(400)->setJSON(['message' => "Invaild EmailId"]);

        }
    }  // ================ IS EMAILVALIDATION ==============
    public function isUpdatedEmailPassword()
    {
        $json = $this->request->getJSON();
        $con = [
            'Email' => $json->Email,
            'Password' => $json->Password
        ];
        $userModel = new CustomersModel();
        $existingUser = $userModel->where('Email', $con['Email'])->first();


        if ($existingUser !== null) {
            $existingUser['Password'] = $con['Password'];
            $userModel->save($existingUser);
            return $this->respond(['message' => 'Email updated successfully']);
        } else {
            return $this->response->setStatusCode(400)->setJSON(['message' => "Invaild EmailId"]);

        }
    }  // ============== ISUPDATEDeMAILPASSWORD ===========

  
public function forgetPassword()
{
    $json = $this->request->getJSON();

    $model = new UsersModel();
    $con = [
        'email' => $json->email,
        'user_type'=> 'customer'
    ];
    $user = $model->where('email', $json->email)->where('user_type', 'customer')->first();
    if ($user) {

        $email = \Config\Services::email();

        $email->initialize([
            'protocol' => 'smtp',
            'SMTPHost' => 'smtp.gmail.com',
            'SMTPPort' => 587,
            'SMTPAuth' => true,
            'SMTPUser' => 'officeinfosystems2024@gmail.com',
            'SMTPPass' => 'msiz gltz miut vtcr',
            'mailType' => 'html',
            'SMTPCrypto' => 'tls',
            'newline' => "\r\n"
        ]);
        $randomPassword = substr(str_shuffle('0123456789'), 0, 4);
        $newPassword = "Planery@$randomPassword";

        $email->setFrom('officeinfosystems2024@gmail.com', 'Planet Nursery');
        $email->setTo($json->email);
        $email->setSubject('Your Temporary Password');

        // Add your logo URL here
        $logoUrl = 'https://nexavault.net/pn-adminapi/uploads/media/1749394443_e8d25091acc4c46eace4.png'; 
        $supportEmail = 'officeinfosystems2024@gmail.com';
        $supportPhone = '+1-234-567-890'; // Replace with your support phone if needed

        $message = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; border: 1px solid #e0e0e0; padding: 24px; background: #fafafa;'>
                <div style='text-align:center; margin-bottom:20px;'>
                    <img src='{$logoUrl}' alt='Company Logo' style='max-width:180px; height:auto;' />
                </div>
                <h2 style='color: #388e3c;'>Temporary Password Request</h2>
                <p>We’ve generated a temporary password for your account. Please use the credentials below to log in:</p>
                <table style='margin: 16px 0;'>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{$json->email}</td>
                    </tr>
                    <tr>
                        <td><strong>Temporary Password:</strong></td>
                        <td>{$newPassword}</td>
                    </tr>
                </table>
                
                <p style='color: #d32f2f;'><strong>Note:</strong> We recommend changing this password immediately after logging in. Please proceed with the below steps.</p>
             
                <ol>
                    <li>Log in using the credentials above.</li>
                    <li>Go to your account settings.</li>
                    <li>Change your password to something secure and memorable.</li>
                </ol>
                <p>If you didn’t request this, please contact our support team at <a href='mailto:{$supportEmail}'>{$supportEmail}</a> or call {$supportPhone}.</p>
                <p style='font-size: 12px; color: #888;'>Planet Nursery Support Team</p>
            </div>
        ";

        $email->setMessage($message);
        $email->send();

        $model->set(['password' => password_hash($newPassword, PASSWORD_BCRYPT)])->where($con)->update();
        $message = [
            'message' => 'Temporary password sent to your email.',
            'status' => 200
        ];
        return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message]);
    } else {
        $message = [
            'message' => 'Invalid Email',
            'status' => 400
        ];
        return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
    }
}
// ...existing code...

    public function changePassword()
    {
        $json = $this->request->getJSON();

        $model = new UsersModel();
        $con = [
            'id' => $json->user_id,
        ];
        $user = $model->where($con)->first();
        if ($user) {
            $con = [
                'id' => $json->user_id
            ];
            $user = $model->where($con)->first();

            if (password_verify($json->old_password, $user['password']) && $user['user_type'] == 'customer' ) {
                $con = [
                    'id' => $json->user_id
                ];
                $model->set(['password' => password_hash($json->new_password,PASSWORD_BCRYPT)])->where($con)->update();
                $message = [
                    'message' => 'Password Changed',
                    'status' => 200
                ];
                return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message]);

            } else {
                $message = [
                    'message' => 'Wrong Password',
                    'status' => 400
                ];
                return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
            }

        } else {
            $message = [
                'message' => 'Invalid User',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
        }
    }
    public function generateCaptcha()
{
    // Clean up expired CAPTCHAs
    $db = \Config\Database::connect();
    $db->query("DELETE FROM captcha_stores WHERE expiration_time < NOW()");

    // Generate new CAPTCHA
    $captchaCode = $this->generateRandomCode(6);
    $imageBytes = $this->generateCaptchaImage($captchaCode);
    $captchaId = uniqid();
    $expirationTime = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    // Store CAPTCHA in database
    $builder = $db->table('captcha_stores');
    $builder->insert([
        'id' => $captchaId,
        'captcha_code' => $captchaCode,
        'expiration_time' => $expirationTime
    ]);

    // Prepare response
    $base64Image = base64_encode($imageBytes);
    $dataUrl = "data:image/png;base64," . $base64Image;

    return $this->respond([
        'captchaId' => $captchaId,
        'imageBase64' => $dataUrl
    ]);
}

// Helper to generate random code
private function generateRandomCode($length)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}

// Helper to generate CAPTCHA image
private function generateCaptchaImage($captchaCode)
{
    $width = 150;
    $height = 40;

    $image = imagecreatetruecolor($width, $height);

    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 128, 128, 128);

    imagefilledrectangle($image, 0, 0, $width, $height, $white);

    // Add text
    $font = 20; // Built-in GD font
    $x = 20;
    $y = 10;
    imagestring($image, $font, $x, $y, $captchaCode, $black);

    // Add line
    // imageline($image, 0, 25, $width, 25, $gray);

    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    return $imageData;
}
public function updateUserName(){
    $json = $this->request->getJSON();
    $userId = $json->user_id ?? null;
    $newName = $json->name ?? null;

    if (!$userId || !$newName) {
        return $this->respond([
            'status' => 'error',
            'message' => 'User ID and new name are required.'
        ], 400);
    }

    $model = new UsersModel();
    $user = $model->find($userId);

    if (!$user) {
        return $this->respond([
            'status' => 'error',
            'message' => 'User not found.'
        ], 404);
    }

    // Update user name
    $model->update($userId, ['name' => $newName]);

    return $this->respond([
        'status' => 'success',
        'message' => 'User name updated successfully.'
    ]);
}
public function validateCaptcha()
{
    $json = $this->request->getJSON();
    $captchaId = $json->captchaId ?? null;
    $captchaCode = $json->userInput ?? null;

    if (!$captchaId || !$captchaCode) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Captcha ID and code are required.'
        ], 400);
    }

    $db = \Config\Database::connect();
    $builder = $db->table('captcha_stores');
    $captcha = $builder
        ->where('id', $captchaId)
        ->where('expiration_time >=', date('Y-m-d H:i:s'))
        ->get()
        ->getRow();

    if ($captcha->captcha_code === $captchaCode) {
        // Optionally, delete the captcha after successful validation
        $builder->where('id', $captchaId)->delete();

        return $this->respond([
            'status' => 'success',
            'message' => 'Captcha validated successfully.'
        ]);
    } else {
        return $this->respond([
            'status' => 'error',
            'message' => 'Invalid or expired captcha.'
        ], 400);
    }
}

}





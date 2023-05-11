<?php

namespace App\Controllers;

use \Firebase\JWT\JWT;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
{
    private $userModel;
    private $profileModel;
    private $twig;

    // date config for jwt
    private $timeExpiration = (3600 * 2); // 2 hours
    private $date_to_renew = (1800); // 30 minutes

    public function __construct()
    {
        $this->userModel = new \App\Models\UserModel();
        # $this->profileModel = new \App\Models\ProfileModel();
    }

    /**
     * login
     *
     * @return ResponseInterface
     */
    public function login()
    {
        $rules = [
            "username" => "required|min_length[5]",
            "password" => "required",
        ];

        $messages = [
            "username" => [
                "required" => "Username is required",
            ],
            "password" => [
                "required" => "password is required"
            ],
        ];

        if ($this->validate($rules, $messages)) {

            $form = $this->request->getVar();

            $user = $this->userModel->where([
                'username' => $form->username
            ])->first();

            if ($user && password_verify($form->password, $user->password)) {

                if ($user->active == 't') {
                    # $profile = $this->profileModel->find($user->profile_id);

                    date_default_timezone_set('America/Sao_Paulo');

                    $iat = time(); // current timestamp value
                    $nbf = $iat;
                    $exp = $iat + $this->timeExpiration; // token expires in 2 hours

                    $payload = [
                        "iss" => "Prosmed API",
                        "aud" => "Prosmed CRM",
                        "iat" => $iat, // issued at
                        "nbf" => $nbf, //not before in seconds
                        "exp" => $exp, // expire time in seconds
                        "data" => [
                            'id' => $user->id,
                            'username' => $user->username,
                            'name' => $user->name,
                            'email' => $user->email,
                            'force_change_passw' => $user->force_change_passw == 't',
                            'photo' => $user->photo,
                        ],
                    ];


                    $algorithm = 'HS256'; // Specify the encryption algorithm

                    $token = JWT::encode($payload, getenv('jwt.secret'), $algorithm);

                    $response = [
                        'status' => 200,
                        'data' => [
                            'token' => $token,
                            'username' => $user->username,
                            'email' => $user->email,
                            'photo' => $user->photo,
                            'first_name' => strtok($user->name, " "),
                            'expiration_date' => date('Y-m-d H:i:s', $exp),
                            'date_to_renew' => date('Y-m-d H:i:s', $nbf + $this->date_to_renew),
                        ]
                    ];

                } else {
                    $response = [
                        'status' => 400,
                        'error' => true,
                        'message' => 'Usuário inativo no sistema',
                        'data' => []
                    ];
                }

            } else {
                $response = [
                    'status' => 400,
                    'error' => true,
                    'message' => 'Usuário ou senha inválido',
                    'data' => []
                ];
            }

        } else {
            $response = [
                'status' => 403,
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' => []
            ];
        }

        if($response['status'] == 200) {
            return $this->respond($response['data'] , $response['status']);
        }else {
            return $this->respond($response['message'], $response['status']);
        }

    }

    /**
     * Faz o logout do usuário
     *
     * @return void
     */
    public function logout()
    {
        $authHeader = $this->request->getHeader('Authorization');
        $token = $authHeader->getValue();

        try {
            $token = str_replace('Bearer ', '', $token);

            $decoded = JWT::decode($token, getenv('jwt.secret'), array("HS256"));

            $currentUser = $decoded->data;

        } catch (\Throwable $e) {
            return $this->respond([
                'status' => ResponseInterface::HTTP_BAD_REQUEST,
                'error' => true,
                'message' => $e->getMessage(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        \App\Services\LogService::save([
            'section' => 'auth',
            'action' => 'logout',
            'detail' => [
                'username' => $currentUser->username
            ],
            'user_id' => $currentUser->id,
        ]);

        $response = [
            'message' => 'Usuário deslogado com sucesso'
        ];

        return $this->respond($response, 200);
    }

    /**
     * Envia email de recuperação de senha
     *
     * @return void
     */
    public function forgotPassword()
    {
        $rules = [
            "email" => "required|valid_email",
        ];

        $messages = [
            "email" => [
                "required" => "Email is required",
            ],
        ];

        if ($this->validate($rules, $messages)) {
            $form = $this->request->getVar();

            $result = $this->userModel->forgotPassword($form->email);
            if (!$result) {
                $response = [
                    'status' => 400,
                    'error' => true,
                    'message' => 'Não foi localizado nenhum usuário com este e-mail.',
                    'data' => []
                ];
            } else {
                $mail = new MailerService();

                $body = $this->twig->render('email/resetPassword.html.twig', [
                    'user' => $result['user'],
                    'token' => $result['token'],
                    'link' => base_url('resetar-senha/' . $result['token'])
                ]);

                $mail->sendMail([
                    'subject' => 'Redefinir Senha do ' . getenv('system.title'),
                    'body' => $body,
                    'to' => 'rcerqueira@gmail.com', //$usuario->email,
                ]);

                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'Nos próximos minutos você receberá um e-mail em ' . $form->email . ' com instruções para redefinir sua senha.',
                ];
            }

        } else {
            $response = [
                'status' => 400,
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' => []
            ];
        }

        return $this->respond($response, $response['status']);
    }


}
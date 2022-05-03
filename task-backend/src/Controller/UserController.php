<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\User;
use App\Entity\Task;
use App\Services\JwtAuth;

class UserController extends AbstractController
{
    public $doctrine;
    public $serializer;
    public function __construct(ManagerRegistry $doctrine, SerializerInterface $serializer){
        $this->doctrine = $doctrine;
        $this->serializer = $serializer;
    }

    public function resjson($data): Response
    {
        //$encoders = [new JsonEncoder()];
        //$normalizers = [new ObjectNormalizer()];
        //$serializer = new Serializer($normalizers, $encoders);
        $json = $this->serializer->serialize($data, 'json');
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function register(Request $request, ManagerRegistry $doctrine): Response{
        // Get the data from the request
        $json = $request->get('json', null);

        // Decode the upcoming json
        $params = json_decode($json);

        // Create a default response
        $data = [
            'status' => 'fail',
            'code' => 400,
            'message' => 'The user have not been created',
            'json' => $params
        ];

        // Check and validate data
        if($json != null){
            $name = (!empty($params->name)) ? $params->name : null;
            $surname = (!empty($params->surname)) ? $params->surname : null;
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [new Email()]);

            if(!empty($email) && count($validate_email) == 0 && !empty($password) && !empty($name) && !empty($surname)){
                $user = new User();
                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);

                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                // Check for the duplicated user
                $entityManager = $doctrine->getManager();

                $user_repo = $doctrine->getRepository(User::class);
                $isset_user = $user_repo->findBy(['email' => $email]);

                if(count($isset_user) == 0){
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'User created successfuly',
                        'user' => $user
                    ];
                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'The user already exists'
                    ];
                }
            }
        }
        // Return a response
        return $this->resjson($data);
    }

    public function login(Request $request, JwtAuth $jwt_auth){
        $json = $request->get('json', null);
        $params =json_decode($json);

        // Array por defecto para devolver
        $data = [
            'status' => 'error',
            'code' => 404,
            'message' => 'El usuario no se ha podido identificar'
        ];

        // Comprobar y validar datos
        if($json != null){
            $email = (!empty($params->email) ? $params->email : null);
            $password = (!empty($params->password) ? $params->password : null);
            $getToken = (!empty($params->getToken) ? $params->getToken : null);

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if(!empty($email) && !empty($password) && count($validate_email) == 0){
                // Cifrar contraseña
                $pwd = hash('sha256', $password);

                // Si todo es valido llamaremos a un servicio jwt
                if($getToken){
                    $signup = $jwt_auth->signup($email, $pwd, $getToken);
                }else{
                    $signup = $jwt_auth->signup($email, $pwd);
                }
                return new JsonResponse($signup);
            }        
        }
        return $this->resjson($data);
    }

    public function update(Request $request, JwtAuth $jwt_auth, ManagerRegistry $doctrine){
        // Recoger la cabecera de autenticacion
        $token = $request->headers->get('Authorization');

        // Crear un metodo para comprobar si el token es correcto
        $authCheck = $jwt_auth->checkToken($token);

        // Repuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Usuario no actualizado',
        ];

        // Si es correcto, hacer la actualizacion del usuario
        if($authCheck){
            // Actualizar al usuario
            // Conesguir entity manager
            $em = $doctrine->getManager();

            // Conseguir los datos del usuario identidicado
            $identity = $jwt_auth->checkToken($token, true);

            // Conseguir el usuario a actualizar completo
            $user_repo = $doctrine->getRepository(User::class);
            $user = $user_repo->findOneBy([
                'id' => $identity->sub
            ]);

            // Recoger datos por post
            $json = $request->get('json', null);
            $params = json_decode($json);

            // Comprobar y validar los datos
            if(!empty($json)){
                if($json != null){
                    $name = (!empty($params->name)) ? $params->name : null;
                    $surname = (!empty($params->surname)) ? $params->surname : null;
                    $email = (!empty($params->email)) ? $params->email : null;

                    $validator = Validation::createValidator();
                    $validate_email = $validator->validate($email, [
                        new Email()
                    ]);

                    if(!empty($email) && count($validate_email) == 0 && !empty($name) && !empty($surname)){
                        // Asignar nuevos datos al objeto del usuario
                        $user->setEmail($email);
                        $user->setName($name);
                        $user->setSurname($surname);

                        // Comprobar duplicados
                        $isset_user = $user_repo->findBy([
                            'email' => $email
                        ]);

                        if(count($isset_user) == 0 || $identity->email == $email){
                            // Guardar cambios en la base de datos
                            $em->persist($user);
                            $em->flush();

                            $data = [
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'User updated',
                                'user' => $user
                            ];
                        }else{
                            $data = [
                                'status' => 'error',
                                'code' => 400,
                                'message' => 'You cannot update this user',
                            ];
                        }
                    }
                }
            }
        }
        return $this->resjson($data);
    }

    public function uploadAvatar(Request $request, JwtAuth $jwt_auth, SluggerInterface $slugger){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'The avatar was not uploaded'
        ];

        $params = $request->files->get('file0', null);
        $token = $request->headers->get('Authorization');
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $image = (!empty($params) ? $params : null);

            $ext = $image->guessExtension();
            if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png'){
                $originalFileName = pathInfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFileName);
                $newFilename = $safeFilename.'-'.uniqId().'.'.$image->guessExtension();

                $identity = $jwt_auth->checkToken($token, true);
                $id = $identity->sub;
                $user = $this->doctrine->getRepository(User::class)->findOneBy(['id' => $id]);
                $user->setImage($newFilename);

                $em = $this->doctrine->getManager();
                $em->persist($user);
                $em->flush();

                try{
                    $image->move($this->getParameter('image_directory'), $newFilename);
                }catch(FileException $e){
                    echo $e;
                    //echo "It failed";
                }

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'user' => $user
                ];
            }
        }

        return $this->resjson($data);
    }

    public function avatar($file = null){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Avatar not found'
        ];

        if($file){
            $newFilename = $file;

            // Two ways to return the file
            $image = new File($this->getParameter('image_directory').$newFilename);
            $img = new BinaryFileResponse($this->getParameter('image_directory').$newFilename);

            $data = [
                'status' => 'success',
                'code' => 200,
                'image' => $img
            ];
        }

        return $img;
        //return $this->resjson($data);
    }
}

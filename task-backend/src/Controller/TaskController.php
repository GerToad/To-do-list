<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Knp\Component\Pager\PaginatorInterface;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Task;
use App\Entity\User;
use App\Services\JwtAuth;

class TaskController extends AbstractController
{
    public $serializer;
    public $doctrine;

    public function __construct(SerializerInterface $serializer, ManagerRegistry $doctrine){
        $this->serializer = $serializer;
        $this->doctrine = $doctrine;
    }

    public function resjson($data): Response
    {
        $json = $this->serializer->serialize($data, 'json', ['groups' => ['user', 'task']]);
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function create(ManagerRegistry $doctrine, JwtAuth $jwt_auth, Request $request, $id=null): Response
    {
        $token = $request->headers->get('Authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Task not created'
        ];

        if($authCheck){
            $json = $request->get('json', null);
            $params = json_decode($json);

            $identity = $jwt_auth->checkToken($token, true);

            if(!empty($json)){
                $user_id = $identity->sub;
                $title = (!empty($params->title)) ? $params->title : null;
                $description = (!empty($params->description)) ? $params->description : null;
                $status = (!empty($params->status)) ? $params->status : null;

                if(!empty($user_id) && !empty($title) && !empty($description)){
                    // Save the object
                    $em = $doctrine->getManager();
                    $user = $doctrine->getRepository(User::class)->findOneBy(['id'=>$user_id]);

                    if($id == null){
                        // This is for creating a new one
                        $task = new Task();
                        $task->setUser($user);
                        $task->setTitle($title);
                        $task->setDescription($description);
                        $task->setStatus("Incomplete");

                        $createdAt = new \Datetime('now');
                        $task->setCreatedAt($createdAt);

                        $em->persist($task);
                        $em->flush();

                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'The task was successfuly created',
                            'task' => $task
                        ];
                    }else{
                        $task = $doctrine->getRepository(Task::class)->findOneBy(['id'=>$id]);

                        if($task && is_object($task)){
                            $task->setTitle($title);
                            $task->setDescription($description);
                            $task->setStatus($status);

                            $em->persist($task);
                            $em->flush();

                            $data = [
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'Task updated',
                                'task' => $task
                            ];
                        }
                    }
                }
            }

        }

        return $this->resjson($data);
    }

    public function tasks(Request $request, JwtAuth $jwt_auth, PaginatorInterface $paginator){
        $token = $request->headers->get('Authorization');

        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->doctrine->getManager();

            // Hacer una consulta  DQL para paginar
            $dql = "SELECT t FROM App\Entity\Task t WHERE t.user = ($identity->sub) ORDER BY t.id DESC";
            $query = $em->createQuery($dql);

            $page = $request->query->getInt('page', 1);
            $items_per_page = 10;

            $pagination = $paginator->paginate($query, $page, $items_per_page);
            $total = $pagination->getTotalItemCount();

            $data = [
                'status' => 'success',
                'code' => 200,
                'total_items_count' => $total,
                'page' => $page,
                'items_per_page' => $items_per_page,
                'total_pages' => ceil($total / $items_per_page),
                'tasks' => $pagination,
                'user_id' => $identity->sub
            ];
        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Tasks not found'
            ];
        }

        return $this->resjson($data);
    }

    public function task(Request $request, JwtAuth $jwt_auth, $id = null){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Task not found'
        ];

        $token = $request->headers->get('Authorization');
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);

            $task = $this->doctrine->getRepository(Task::class)->findOneBy(['id' => $id]);

            if($task && is_object($task) && $identity->sub == $task->getUser()->getId()){
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'task' => $task
                ];
            }
        }

        return $this->resjson($data);
    }

    public function remove(Request $request, JwtAuth $jwt_auth, $id=null){

        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Task not found'
        ];

        $token = $request->headers->get('Authorization' );
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);
            $em = $this->doctrine->getManager();
            $task = $this->doctrine->getRepository(Task::class)->findOneBy(['id' => $id]);

            if($task && is_object($task) && $identity->sub == $task->getUser()->getId()){
                $em->remove($task);
                $em->flush();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'task' => $task,
                ];
            }
        }

        return $this->resjson($data);
    }

    public function taskCheck(Request $request, JwtAuth $jwt_auth, $id=null){
        $token = $request->headers->get('Authorization');
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $json = $request->get('check', null);
            $params = json_decode($json);
            $identity = $jwt_auth->checkToken($token, true);
            $task = $this->doctrine->getRepository(Task::class)->findOneBy(['id'=>$id]);
            $em = $this->doctrine->getManager();

            if($task && is_object($task)){
                $status = (!empty($params->status)) ? $params->status : null;
                $task->setStatus($status);

                $em->persist($task);
                $em->flush();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Task checked',
                    'task' => $task
                ];
            }
        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'task' => 'Not possible',
            ];
        }

        return $this->resjson($data);
    }

    public function search(Request $request, JwtAuth $jwt_auth, PaginatorInterface $paginator, $search=null){
        $token = $request->headers->get('Authorization');

        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->doctrine->getManager();

            // Hacer una consulta  DQL para paginar
            $dql = "SELECT t FROM App\Entity\Task t WHERE t.title LIKE '%$search%' AND t.user = ($identity->sub) ORDER BY t.id DESC";
            $query = $em->createQuery($dql);

            $page = $request->query->getInt('page', 1);
            $items_per_page = 10;

            $pagination = $paginator->paginate($query, $page, $items_per_page);
            $total = $pagination->getTotalItemCount();

            $data = [
                'status' => 'success',
                'code' => 200,
                'total_items_count' => $total,
                'page' => $page,
                'items_per_page' => $items_per_page,
                'total_pages' => ceil($total / $items_per_page),
                'tasks' => $pagination,
                'user_id' => $identity->sub
            ];
        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Tasks not found'
            ];
        }

        return $this->resjson($data);
    }

}

<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(protected UserRepository $userRepository)
    {
    }

    public function find($id): Model
    {
        return $this->userRepository->find($id);
    }

    public function store($data): Model
    {
        $user = $this->userRepository->create($data);

        if (! $user->id) {
            throw new Exception();
        }

        $user->assignRole(RoleEnum::WRITER);

        return $user;
    }

    public function update($id, $post): array
    {
        $post['usualt'] = Auth('user')->id();
        DB::beginTransaction();
        try {
            $success = $this->userRepository->update($post, $id, 'idusuario');
            if ($success) {
                DB::commit();
                return ['status' => Status::SUCCESS];
            } else {
                DB::rollback();
                return ['status' => Status::ERROR, 'message' => 'Ocorreu um erro. Tente novamente e caso o erro persista, contate o administrador do sistema (1)'];
            }
        } catch (\Exception $e) {
            DB::rollback();
            return ['status' => Status::ERROR, 'message' => 'Ocorreu um erro. Tente novamente e caso o erro persista, contate o administrador do sistema (2)'];
        }
    }

    public function delete($id)
    {
        /** @var UserPasswordService $userPasswordService */
        $userPasswordService = App()->make(UserPasswordService::class);

        DB::beginTransaction();
        try {
            $user = $this->find($id);
            $result = $userPasswordService->findByIdusuario($id);
            foreach ($result as $item) {
                $item->delete();
            }
            $user->delete();

            DB::commit();
            return ['status' => Status::SUCCESS];
        } catch (\Exception $e) {
            DB::rollback();
            return ['status' => Status::ERROR, 'message' => 'Ocorreu um erro. Tente novamente e caso o erro persista, contate o administrador do sistema (1)'];
        }
    }

    /**
     * Cria solicitação para recuperação de senha.
     * @param $data
     * @throws \Throwable
     */
    public function recoveryPassword($data)
    {
        /** @var UserPasswordService $userPasswordService */
        $userPasswordService = App()->make(UserPasswordService::class);
        $userPasswordService->recoveryRequest($data['email']);
    }

    /**
     * Altera a senha do usuário.
     * @param $data
     * @param $id
     * @return array
     */
    public function changePassword($data, $id)
    {
        $post = [
            'senha' => bcrypt($data),
        ];
        DB::beginTransaction();
        try {
            $this->userRepository->update($post, $id, 'idusuario');
            DB::commit();
            return ['status' => Status::SUCCESS];
        } catch (\Exception $exception) {
            DB::rollback();
            return ['status' => Status::ERROR, 'message' => 'Ocorreu um erro. Tente novamente e caso o erro persista, contate o administrador do sistema (1)'];
        }
    }
}

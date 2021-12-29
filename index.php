<?php

/* Задание 1. Найти и указать в проекте Front Controller и расписать классы, которые с ним взаимодействуют.

        Front Controller - это файл расположенный по пути app/Kernel.
        Классы взаимодействующие с Front   Controller(app/Kernel):

         Классы, которые вызываются непосредственно в самом классе Kernel
            Framework\Registry;
            Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
            Symfony\Component\DependencyInjection\ContainerBuilder;
            Symfony\Component\Config\FileLocator;
            Symfony\Component\HttpKernel\Controller\ControllerResolver;
            Symfony\Component\HttpKernel\Controller\ArgumentResolver;
            Symfony\Component\HttpFoundation\Request;
            Symfony\Component\HttpFoundation\Response;
            Symfony\Component\HttpFoundation\Session\Session;
            Symfony\Component\Routing\Exception\ResourceNotFoundException;
            Symfony\Component\Routing\Matcher\UrlMatcher;
            Symfony\Component\Routing\RequestContext;
            Symfony\Component\Routing\RouteCollection;

        Классы, которые вызываются при вызове Registry  из класса Kernel
            Symfony\Component\DependencyInjection\ContainerBuilder;
            Symfony\Component\Routing\Generator\UrlGenerator;
            Symfony\Component\Routing\RequestContext;
            Symfony\Component\Routing\RouteCollection;*/


 /* Задание 2. Найти в проекте паттерн Registry и объяснить, почему он был применён.

 Создается контейнер. В него складываются данные. Обеспечивается возможность доступа 
к этим данным из других частей программы. */

паттерн Registry - app/framework/registry.php 

 <?php

 declare(strict_types = 1);

 namespace Framework;

 use Symfony\Component\DependencyInjection\ContainerBuilder;
 use Symfony\Component\Routing\Generator\UrlGenerator;
 use Symfony\Component\Routing\RequestContext;
 use Symfony\Component\Routing\RouteCollection;

 class Registry
 {
     /**
      * @var ContainerBuilder
      */
     private static $containerBuilder;

     /**
      * Добавляем контейнер для работы реестра
      *
      * @param ContainerBuilder $containerBuilder
      * @return void
      */
     public static function addContainer(ContainerBuilder $containerBuilder): void
     {
         static::$containerBuilder = $containerBuilder;
     }

     /**
      * Получаем данные из конфигурационного файла
      *
      * @param string $name
      * @return mixed
      */
     public static function getDataConfig(string $name)
     {
         if (!static::$containerBuilder->hasParameter($name)) {
             throw new \InvalidArgumentException('Unknown config variable ' . $name);
         }

         return static::$containerBuilder->getParameter($name);
     }

     /**
      * Рендерим страницу по названию роута
      *
      * @param string name
      * @param array $parameters
      * @return string
      */
     public static function getRoute(string $name, array $parameters = []): string
     {
         /** @var RouteCollection $routeCollection */
         $routeCollection = static::$containerBuilder->get('route_collection');

         $urlGenerator = new UrlGenerator($routeCollection, new RequestContext());
         try {
             return $urlGenerator->generate($name, $parameters);
         } catch (\Exception $e) {
             throw new \InvalidArgumentException('Unknown route name ' . $name);
         }
     }
 }



 /* Задание 3. Добавить во все классы Repository использование паттерна Identity Map вместо постоянного генерирования сущностей.*/



namespace Model\Repository;

use Model\Entity;

class User
{
    private $identityMap = [];

    /**
     * Получаем пользователя по ID
     *
     * @param int $id
     * @return Entity\User|null
     */
    public function getById(int $id): ?Entity\User
    {
        foreach ($this->identityMap as $user) {
            if ($user['id'] === $id) {
                return $this->createUser($user);
            }
        }

        foreach ($this->getDataFromSource(['id' => $id]) as $user) {
            return $this->createUser($user);
        }

        return null;
    }

    /**
     * Получаем пользователя по логину
     *
     * @param string $login
     * @return Entity\User
     */
    public function getByLogin(string $login): ?Entity\User
    {
        foreach ($this->identityMap as $user) {
            if ($user['login'] === $login) {
                return $this->createUser($user);
            }
        }

        foreach ($this->getDataFromSource(['login' => $login]) as $user) {
            if ($user['login'] === $login) {
                return $this->createUser($user);
            }
        }

        return null;
    }

    /**
     * Фабрика создания сущности пользователя
     *
     * @param array $user
     * @return Entity\User
     */
    private function createUser(array $user): Entity\User
    {
        $role = $user['role'];
        $this->identityMap[] = $user;

        return new Entity\User(
            $user['id'],
            $user['name'],
            $user['login'],
            $user['password'],
            new Entity\Role($role['id'], $role['title'], $role['role'])
        );
    }

    /**
     * Получение пользователей из источника данных
     *
     * @param array $search
     *
     * @return array
     */
    private function getDataFromSource(array $search = [])
    {
        $admin = ['id' => 1, 'title' => 'Super Admin', 'role' => 'admin'];
        $user = ['id' => 1, 'title' => 'Main user', 'role' => 'user'];
        $test = ['id' => 1, 'title' => 'For the test', 'role' => 'test'];

        $dataSource = [
            [
                'id' => 1,
                'name' => 'Super Admin',
                'login' => 'root',
                'password' => '$2y$10$GnZbayyccTIDIT5nceez7u7z1u6K.znlEf9Jb19CLGK0NGbaorw8W',
                'role' => $admin
            ],
            [
                'id' => 2,
                'name' => 'Sanita Mari',
                'login' => 'sanitamari',
                'password' => '$2y$10$j4DX.lEvkVLVt6PoAXr6VuomG3YfnssrW0GA8808Dy5ydwND/n8DW', 
                'role' => $user
            ],
            [
                'id' => 3,
                'name' => 'Chernuyk Marina Vladimirovna',
                'login' => 'marica',
                'password' => '$2y$10$TcQdU.qWG0s7XGeIqnhquOH/v3r2KKbes8bLIL6NFWpqfFn.cwWha',
                'role' => $user
            ],
            [
                'id' => 4,
                'name' => 'Petrov Ivan Fedorovich',
                'login' => 'petrov-21',
                'password' => '$2y$10$vQvuFc6vQQyon0IawbmUN.3cPBXmuaZYsVww5csFRLvLCLPTiYwMa', 
                'role' => $test
            ],
        ];

        if (!count($search)) {
            return $dataSource;
        }

        $productFilter = function (array $dataSource) use ($search): bool {
            return (bool) array_intersect($dataSource, $search);
        };

        return array_filter($dataSource, $productFilter);
    }
}

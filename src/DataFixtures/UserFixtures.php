<?php

namespace App\DataFixtures;

use App\Entity\Relation;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;




class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    private UsersRepository $usersRepository;

    public function __construct(UserPasswordHasherInterface $hasher, UsersRepository $usersRepository)
    {
        $this->hasher = $hasher;
        $this->usersRepository = $usersRepository;
    }

    public function load(ObjectManager $manager ): void
    {
        $admin = $this->createAdmin();

        $manager->persist($admin);
        $manager->flush();
        $user = $this->usersRepository->findOneBy(['id' => $admin]);
        $relation = new Relation();
        $relation->setUser($user);
        
        $manager->persist($relation);
        $manager->flush();


    }

    public function createAdmin() : Users
    {

        $user = new Users();
        $passwordHashed = $this->hasher->hashPassword($user, "Deuzyriel$2023");

        $user->setFirstName('Thaj Yeng');
        $user->setLastName('Vang');
        $user->setEmail('deuzyriel@gmail.com');
        $user->setRoles([]);
        $user->setPassword($passwordHashed);
        return $user;
    }

}

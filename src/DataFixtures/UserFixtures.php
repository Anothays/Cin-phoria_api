<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserStaff;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public static function getGroups(): array
    {
        return ['users'];
    }

    public function load(ObjectManager $manager)
    {

        // CREATE STAFF USERS
        $users_staff_data = json_decode(file_get_contents(__DIR__ . '/users_staff.json'), true);
        $usersStaff = [];
        foreach ($users_staff_data as $key => $value) {
            $userStaff = (new UserStaff())
                ->setEmail($value['email'])
                ->setRoles($value['roles'])
                ->setFirstname($value['firstname'])
                ->setLastname($value['lastname'])
                ;
            $userStaff->setPassword($this->passwordHasher->hashPassword($userStaff, $value['password']));
            $usersStaff[] = $userStaff;
            $manager->persist($userStaff);
            // $this->addReference("USER_STAFF_{$key}", $userStaff);
        }

        // CREATE CUSTOMERS USERS
        $users_data = json_decode(file_get_contents(__DIR__ . '/users.json'), true);
        $users = [];
        foreach ($users_data as $key => $value) {
            $user = (new User())
                ->setEmail($value['email'])
                ->setRoles($value['roles'])
                ->setFirstname($value['firstname'])
                ->setLastname($value['lastname'])
                ->setVerified(true)
                ;
            $user->setPassword($this->passwordHasher->hashPassword($user, $value['password']));
            $users[] = $user;
            $manager->persist($user);
            // $this->addReference("USER_{$key}", $users);
        }
        
        $manager->flush();
    }
}
<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordHashService
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    // Hash the password with a secure algorithm and automatic salting/peppering
    public function hashPassword(User $user, string $plainPassword): string
    {
        return $this->passwordHasher->hashPassword($user, $plainPassword);
    }

    // Verify the password against the hashed version
    public function isPasswordValid(User $user, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }

    // Check if the password needs to be rehashed (e.g., if the algorithm has been updated)
    public function needsRehash(User $user): bool
    {
        return $this->passwordHasher->needsRehash($user);
    }
}

<?php

namespace Users\Authentication;

use Kdyby\Doctrine\EntityManager;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Http\IRequest;
use Nette\Object;
use Users\User;

class UserAuthenticator extends Object implements IAuthenticator
{
    /** @var array */
    //public $onLoggedIn = [];

    /** @var IRequest */
    private $httpRequest;

    /** @var EntityManager  */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager,
        IRequest $httpRequest
    ) {
        $this->httpRequest = $httpRequest;
        $this->entityManager = $entityManager;
    }

    /**
     * Performs an authentication against e.g. database.
     * and returns IIdentity on success or throws AuthenticationException
     * @return IIdentity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;

        $user = $this->entityManager
                     ->getRepository(User::class)
                     ->findOneBy(['email' => $email]);

        if ($user === null) {
            throw new AuthenticationException('Wrong E-mail address');
        }

        if (!Passwords::verify($password, $user->password)) {
            throw new AuthenticationException('Wrong password');

        } elseif (Passwords::needsRehash($user->password)) {

            $user->password = Passwords::hash($password);
        }

        //$this->onLoggedIn($user);

        return new FakeIdentity($user->getId(), get_class($user));
    }
}
<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Users\Log\Subscribers;

use Users\FrontModule\Presenters\AuthPresenter;
use Users\Services\RolePermissionsPersister;
use Users\Authentication\UserAuthenticator;
use Users\Services\RolePersister;
use Log\Services\AppEventLogger;
use Users\Authorization\Role;
use Kdyby\Events\Subscriber;
use Nette\Object;
use Users\User;

class UserSubscriber extends Object implements Subscriber
{
    /** @var AppEventLogger */
    private $appEventLogger;

    /** @var User */
    private $user;


    public function __construct(
        AppEventLogger $appEventLogger,
        \Nette\Security\User $user
    ) {
        $this->appEventLogger = $appEventLogger;
        $this->user = $user->getIdentity();
    }



    function getSubscribedEvents()
    {
        return [
            UserAuthenticator::class . '::onLoggedIn',
            AuthPresenter::class . '::onLoggedOut',

            RolePersister::class . '::onSuccessRoleCreation',
            RolePermissionsPersister::class . '::onSuccessRolePermissionsEditing'
        ];
    }


    public function onLoggedIn(User $user, $ip)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] <b>has LOGGED INTO</b> system with IP %s',
                     $user->getId(),
                     $user->getUsername(),
                     $ip
                 ),
                'user_login',
                 $user->getId()
             );
    }


    public function onLoggedOut(User $user, $ip)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] <b>has LOGGED OUT</b> of the system with IP %s',
                     $user->getId(),
                     $user->getUsername(),
                     $ip
                 ),
                'user_logout',
                 $user->getId()
             );
    }


    public function onSuccessRoleCreation(Role $role)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] <b>has CREATED</b> Role [%s#%s]',
                     $this->user->getId(),
                     $this->user->getUsername(),
                     $role->getId(),
                     $role->getName()
                 ),
                'user_role_creation',
                 $this->user->getId()
             );
    }


    public function onSuccessRolePermissionsEditing(Role $role)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] <b>has EDITED</b> permissions of Role [%s#%s]',
                     $this->user->getId(),
                     $this->user->getUsername(),
                     $role->getId(),
                     $role->getName()
                 ),
                'user_role_editing',
                 $this->user->getId()
             );
    }

}
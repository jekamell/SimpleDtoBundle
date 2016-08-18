# SimpleDtoBundle documentation
## Base crud functionality

SimpleDtoBundle comes with **AbstractController** where base _CRUD_ functions are implemented.

User AbstractController methods for:

| Method         | Description                                                                                |
|----------------|--------------------------------------------------------------------------------------------|
| createResource | Create resource. Dto data extracted from request body content and appends to passed entity |
| readResource   | Read resource                                                                              |
| updateResource | Update passed resource from Request content                                                |
| deleteResource | Delete resource                                                                            |
| listResources  | Read collection of resources                                                               |


### Simple example of UserController for demonstrate base crud actions

```php
<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Mell\Bundle\SimpleDtoBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    /** @return string */
    protected function getDtoType()
    {
        return 'UserDto';
    }

    /** @return array */
    protected function getAllowedExpands()
    {
        return ['AddressDto'];
    }

    /**
     * @return string
     */
    protected function getEntityAlias()
    {
        return 'AppBundle:User';
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        
        return $this->createResource($request, $user);
    }

    /**
     * @param User $user
     * @return Response
     */
    public function readAction(User $user)
    {
        return $this->readResource($user);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function updateAction(Request $request, User $user)
    {
        return $this->updateResource($request, $user);
    }

    /**
     * @param User $user
     * @return Response
     */
    public function deleteAction(User $user)
    {
        return $this->deleteResource($user);
    }

    /**
     * @return Response
     */
    public function listAction()
    {
        return $this->listResources($this->getQueryBuilder());
    }
}
```

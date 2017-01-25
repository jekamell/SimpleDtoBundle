# SimpleDtoBundle documentation
## Dto embedding

### Prerequirements
```yml
# path/to/your/config/dto.yml
UserDto:
    fields:
        id: 
            type: integer
        # other fields declaration
    expands:
        address:
            type: AddressDto
      
AddressDto:
    fields:
        id:
            type: integer
        country:
            type: string
        city:
            type: string
```

```php
class UserController extends AbstractController
{
    /** @return array */
    protected function getAllowedExpands()
    {
        return ['address'];
    }
}

```

### Usege
You can require to embed related object for base CRUD operations. For example if your User object has relation to Address object, you can embed Address object to result with User one:
```
GET http://my-awesome-api.com/api/v1/users/1?_expand=address
```

```json
{
  "id": 1,
  "email": "john.doe@mail.com",
  "firstname": "John",
  "lastname": "Doe",
  "active": true,
  "roles": [],
  "dateCreated": "1011-11-11T11:11:11+00:00",
  "dateUpdated": "2011-11-11T11:11:11+00:00",
  "_expands": {
      "address": {
        "id": 1,
        "country": "US",
        "city": "New York"
      }
    }
}
```
Its also possible to require few embeds: just separate them by ```,```:
```
GET http://my-awesome-api.com/api/v1/users/1?_expand=address,company
```
In case when embed object is ```null```is will be not present in result
#### Embedding from repository method
Its also possible to embed some collection or single object from repository method:
```yml
# path/to/your/config/dto.yml
UserDto:
    fields:
        id:
            type: integer
        # other fields declaration
    expands:
        address:
            type: AddressDto
            getter:
                repository: AppBundle:User
                method: someRepositoryMethod
                arguments:
                    - "object"
                    - "@some_service"
                    - "%some_param%"
                    - "someString"
```
In this case your repository method should looks like:
```php
<?php

namespace AppBundle\Repository;

/**
 * Class UserRepository
 * @package AppBundle\Repository
 */
class UserRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param SomeService $service
     * @param $param
     * @param $string
     * @return Address[]
     */
    public function someRepositoryMethod(User $user, SomeService $service, $param, $string)
    {
        // some logic here to retrieve Address objects

        return $addressObjects;
    }
}
```

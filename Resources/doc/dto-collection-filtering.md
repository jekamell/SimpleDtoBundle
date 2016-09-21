# SimpleDtoBundle documentation
## Dto collection filtering

You can require to filter output collection by adding ```_filters``` GET param:
```
GET http://my-awesome-api.com/api/v1/users?_filters=email:john.doe@mail.com
```
Filtering param and value separates via operation alias. You can use next types of operations:

| Operation alias   | SQL operation    | Example           |
|-------------------|------------------|-------------------|
| :                 | =                | name:John         |
| !:                | != or <>         | name!:John        |
| <:                | <                | age<:15           |
| >:                | >                | age>:15           |
| <=:               | <=               | age<=:15          |
| >=:               | >=               | age>=:15          |
| :({val1},{val2})  | IN               | name:(John,Jane)  |
| !:({val1},{val2}) | NOT IN           | name!:(John,Jane) |

Available filers should be defined in *defaults* section of routing:
```yaml
# config/routing.yml
users_list:
    path: /api/v1/users
    defaults:
        _controller: AppBundle:User:list
        _filters:
            - id
            - email
            - name
    methods: [GET]
```


You can even use few filters. In this case filters should be separated by | and will be joined with ```AND``` SQL operand:
```
GET http://my-awesome-api.com/api/v1/users?_filters=name:John|cityId:(1,3,5)
```

For SQL context this filter will be represented as:
```sql
... WHERE name='John' AND cityId IN (1,3,5);
```

### Applying filters 
To access required filters you can simple highlight ```ApiFilterCollection``` in controller action and pass it to ```AbstractController::listResources``` method:

```php
<?php

namespace AppBundle\Controller;

use Mell\Bundle\SimpleDtoBundle\Model\ApiFilter;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollection;
use Mell\Bundle\SimpleDtoBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 * @package AppBundle\Controller
 */
class UserController extends AbstractController
{
   // ~~~
    
    /**
     * @param ApiFilterCollection $apiFilters
     * @return Response
     */
    public function listAction(ApiFilterCollection $apiFilters)
    {
        // You can modify filters here for your needs
        $apiFilters->append(new ApiFilter('active', ApiFilter::OPERATION_EQUAL, 1));
        
        return $this->serializeResponse($this->listResources($this->getQueryBuilder(), $apiFilters));
    }  
}
```
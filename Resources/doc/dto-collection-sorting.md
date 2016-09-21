# SimpleDtoBundle documentation
## Dto collection sorting

You can require to sort output collection by adding ```_sort``` GET param:
```
GET http://my-awesome-api.com/api/v1/users?_sort=id.desc
```

```json
[
    {
        "id": 3,
        "email": "john.doe@mail.com",
        "firstname": "John",
        "lastname": "Doe",
        "active": true,
        "roles": [],
        "dateCreated": "1011-11-11T11:11:11+00:00",
        "dateUpdated": "2011-11-11T11:11:11+00:00"
    },
        {
        "id": 2,
        "email": "john.smith@mail.com",
        "firstname": "John",
        "lastname": "Snith",
        "active": true,
        "roles": [],
        "dateCreated": "1011-11-11T11:11:11+00:00",
        "dateUpdated": "2011-11-11T11:11:11+00:00"
    },
        {
        "id": 1,
        "email": "vasya.pupkin@mail.com",
        "firstname": "Vasiliy",
        "lastname": "Pupkin",
        "active": true,
        "roles": [],
        "dateCreated": "1011-11-11T11:11:11+00:00",
        "dateUpdated": "2011-11-11T11:11:11+00:00"
    },
]
```

The ```_sort``` value should follow the next pattern: 

```
_sort={field1}.{direction},{field2}.{direction},{fieldN}.{direction}
```

Direction can be one of ```asc``` or ```desc```. You even can ommit asc direction: ```_sort=id```. In this case the direction will be forced to ```asc```

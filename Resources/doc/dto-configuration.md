# SimpleDtoBundle documentation
## Dto configuration
### Dto configuration file example
```yml
UserDto:
    fields:
        id:
            type: integer
            readonly: true
            description: Unique identifier
        email:
            type: string
            required: true
            description: Email address
        firstname:
            type: string
            required: true
            description: First name
            getter: getName
            setter: setName
        lastname:
            type: string
            required: true
            getter: getSurName
            setter: getSurName
            description: Last name
        active:
            type: boolean
            description: Whether the user is active
        password:
            type: string
            groups:
                - create
                - update
            required: true
            description: Password
        roles:
            type: array
            description: Assigned roles
        dateCreated:
            type: datetime
            readonly: true
            description: Date when user was created
        dateUpdated:
            type: datetime
            readonly: true
            description: Date when user was updated
    expands:
        address:
            type: AddressDto
            getter: getLocation
AddressDto:
    fields:
        id: 
            type: integer
            readonly: true
            description: Unique identifier
        country:
            type: string
            description: Country code in A2(ISO) format
        city:
            type: string
            description: City tytle
```

Each dto config consists of fields(required) and expands(optional) sections.

Fields sections is a collection of blocks:

| Field       | Required | Description                                                                                                     |
|-------------|----------|-----------------------------------------------------------------------------------------------------------------|
| type        | Yes      | Property type                                                                                                   |
| readonly    | No       | Whether the field is read only                                                                                  |
| groups      | No       | The collection of groups where current fields is accessible                                                     |
| required    | No       | Whether the fields is required (used for NelmioApiDoc highlight only)                                           |
| getter      | No       | Field getter to access field value                                                                              |
| setter      | No       | Field setter to assign field value                                                                              |
| description | No       | Field description (used for NelmioApiDocBundle). The field name will be used in case when description is empty  |


Expands section is collection of blocks:

|Field       | Required | Description                         |
| type       | Yes      | Expand type (one of dto item)       |
| getter     | No       | Field getter to access expand value |
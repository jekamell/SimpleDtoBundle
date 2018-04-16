# SimpleDtoBundle documentation
## Relation management

```yml
AppBundle\Entity\UserRole:
    attributes:
        id:
            type: integer
            groups: ['read']
            description: Identifier
        userId:
            type: integer
            required: true
            groups: ['create', 'read', 'update']
            description: Related user identifier
        roleId:
            type: string
            required: true
            groups: ['create', 'read', 'update']
            description: Related role identifier
        dateCreated:
            type: datetime
            groups: ['read']
            description: Date when entity was created
        dateUpdated:
            type: datetime
            groups: ['read']
            description: Date when entity was updated
    relations:
        user:
            groups: ['create', 'update'] # optional
            targetEntity:
                class: AppBundle\Entity\User
                attribute: id # optional. Default: id
            attribute: userId
            repositoryMethod: findOneBy # optional
            setter: setUser # optional
        role:
            groups: ['create']
            targetEntity:
                class: AppBundle\Entity\Role
                attributes: id
            attribute: roleId
```
## Disable relation management
```yml
simple_dto: 
    relation_handling_enabled: false
```
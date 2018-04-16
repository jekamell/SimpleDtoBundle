# SimpleDtoBundle documentation
## Fields filtering

You can specify response fields in case when whole fields collection is not required.

Request example **without** fields filtering:
```
GET http://my-awesome-api.com/api/v1/users/1
```

```yaml
{
  "id": 1,
  "email": "john.doe@mail.com",
  "firstname": "John",
  "lastname": "Doe",
  "active": true,
  "roles": [],
  "dateCreated": "1011-11-11T11:11:11+00:00",
  "dateUpdated": "2011-11-11T11:11:11+00:00"
}
```

Request example **with** fields filtering:
```
GET http://my-awesome-api.com/api/v1/users/1?_fields=id,email
```

```yaml
{
  "id": 1,
  "email": "john.doe@mail.com"
}
```
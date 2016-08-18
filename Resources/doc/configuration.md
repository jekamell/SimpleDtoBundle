# SimpleDtoBundle documentation
## Configuration

### Minimal configuration example
```yml
simple_dto:
    dto_config_path: "@AppBundle/Resources/config/dto.yml"
```

### Full configuration example
```yml
simple_dto:
    # Path to dto config. Alias can be used.
    dto_config_path: "@AppBundle/Resources/config/dto.yml"
    # Path to jwt public key.
    jwt_public_path: "%kernel.root_dir%/app/config/jwt_public.pem"
    # Get param for require response fields
    param_fields: _fields 
    # Get param for require response fields
    param_expands: _expands 
    # Get param for define collection limit
    param_limit: _limit 
    # Get param for define collection offset
    param_offset: _offset 
    # Get param for define collection sorting
    param_sort: _sort 
```
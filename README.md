# Сборник процессоров для удобства работы с zircote/swagger-php


1. ## AddSchemaToQueryParameters
   Добавляет свойства схемы как query параметры  
    Пример:
    ```php
    #[Schema(
        properties: [
            new Property(property: 'page', type: 'integer', default: 1),
            new Property(property: 'perPage', type: 'integer', default: 50)
        ]
    )]
    class Pagination{...}
   
   
    #[Get(
        path: "/api/v1/posts",
        x: [
            AddSchemaToQueryParameters::REF => [Pagination::class]
        ]
    )]
    ```
    Преобразуется в 
    ```php

    #[Get(
        path: "/api/v1/posts",
        parameters: [
            new Parameter(name: 'page', in: 'query', schema: new Schema(type: 'integer', default: 1)),
            new Parameter(name: 'perPage', in: 'query', schema: new Schema(type: 'integer', default: 50)),
        ]
    )]
    ```

2. ## EnsureRequiredProperties
   Проставляет в схему обязательность свойства  
   Пример:
    ```php
    #[Schema(
        properties: [
            new Property(property: 'requiredProp1', type: 'integer'),
            new Property(property: 'requiredProp2', type: 'integer'),
            new Property(property: 'requiredProp3', type: 'integer'),
            new Property(property: 'nonRequiredProp', type: 'integer', nullable: true)
        ]
    )]
    ```
   Преобразуется в
    ```php
    #[Schema(
        required: ['requiredProp1', 'requiredProp2', 'requiredProp3'],
        properties: [
            new Property(property: 'requiredProp1', type: 'integer'),
            new Property(property: 'requiredProp2', type: 'integer'),
            new Property(property: 'requiredProp3', type: 'integer'),
            new Property(property: 'nonRequiredProp', type: 'integer', nullable: true)
        ]
    )]
    ```

2. ## GenerateSchemaProperties
   Добавляет аннотации свойств публичным свойствам класса с атрибутом GenerateSchema  
   Пример:
    ```php
    #[GenerateSchema]
    class Pagination
    {
        public function __construct(
            public int $page = 1,
            public int $perPage = 50,
        )
        {
        }
    }
    ```
   Преобразуется в
    ```php
    #[Schema]
    class Pagination
    {
        public function __construct(
            #[Property]
            public int $page = 1,
            #[Property]
            public int $perPage = 50,
        )
        {
        }
    }
    ```

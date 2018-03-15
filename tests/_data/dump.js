db = db.getSiblingDB('project_tests');

db.getCollection("users").insert([
    { 
        "_id" : ObjectId("5923c0e936b3940c14000029"), 
        "first_name" : "John", 
        "last_name" : "Doe", 
        "email" : "test-user@example.com", 
        "password" : "$2y$10$aUj3n66aB9DQRUXfQoo/cu0zaLoYGWKbIE.3km0AVOs.tZAH14xHa", 
        "created_date" : ISODate("2017-04-30T04:58:58.000+0000"),
        "access_level" : "1",
        "active" : true
    },
    { 
        "_id" : ObjectId("593add6559049b192ced3a64"), 
        "first_name" : "Admin", 
        "last_name" : "Doe", 
        "email" : "test-admin@example.com", 
        "password" : "$2y$10$aUj3n66aB9DQRUXfQoo/cu0zaLoYGWKbIE.3km0AVOs.tZAH14xHa", 
        "created_date" : ISODate("2017-04-30T04:58:58.000+0000"),
        "access_level" : "100",
        "active" : true
    },
    { 
        "_id" : ObjectId("593a33e759049b21c48fdf0d"), 
        "first_name" : "Johan", 
        "last_name" : "Dohan", 
        "email" : "test-not-active-user@example.com", 
        "password" : "$2y$10$aUj3n66aB9DQRUXfQoo/cu0zaLoYGWKbIE.3km0AVOs.tZAH14xHa", 
        "created_date" : ISODate("2017-04-30T04:58:58.000+0000"),
        "access_level" : "1",
        "active" : false
    },
    { 
        "_id" : ObjectId("5941471a59049b1a00d5e964"), 
        "first_name" : "User", 
        "last_name" : "Deleted", 
        "email" : "test-deleted-user@example.com", 
        "password" : "$2y$10$aUj3n66aB9DQRUXfQoo/cu0zaLoYGWKbIE.3km0AVOs.tZAH14xHa", 
        "created_date" : ISODate("2017-04-30T04:58:58.000+0000"),
        "access_level" : "1",
        "active" : true,
        "_deleted" : true
    }
]);

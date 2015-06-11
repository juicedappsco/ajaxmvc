INTRODUCTION:     
This is a Convention Based MVC framework - including full ORM - that is fully compatible wth WordPress, currently it only supports
AJAX Requests but it will soon be a fully functioning traditional MVC (a standard conventional MVC router is already written and
functioning, it needs to be integrated with the WordPress native routing system, a class will probably be designed to handle this
integration very soon). 

INSTALLATION:
This framework is built as an mu-plugin and simply needs to be uploaded to the wp-content directory, 
no other installation is required and any plugin residing in the plugins folder may use it successfully as long as 
they follow the naming conventions outlined by this framework.

THIS IS NOT AN ATTEMPT TO REWRITE WORDPRESS (not yet any way -- haha jk -- or not lol):
As everyone knows WordPress uses an Event Based Architecture and there has been an outcry against other attempts at 
creating an MVC architecture over the WP Core and existing Event Based Architecture. I have seen successful integration of Events 
in an MVC framework like Magento, and believe that a successful integration of an MVC framework for plugins would be of great use to 
developers familiar with MVC - as MVC appears to be the standard in the industry. As I have seen a recent trend of some developers 
using WP as an application framework, a successful MVC integration could potentially make WP a well rounded and robust Web Application Framework.

MVC:
The point of this framework, besides the above mentioned details is to abstract away many of the common configuration necessities required by other MVC frameworks
like Zend, Laravel, Cake etc.

There are only two hard things in Computer Science: cache invalidation and NAMING THINGS. -- Phil Karlton

If you are able to name things in such a way that the convention cleverly mirrors the mechanics and logic that a request dictates, many of these
configurations can be abstracted away, this framework is an attempt to solve that problem. Don't worry, for all of you configuration lovers out there, 
there will soon be a single PHP based configuration file - inspired by the Zend Framework (why involve the complexity of another syntax such as XML or JSON) - 
which will allow the developer to map specific requests to any controller location providing an alternative to following the suggested naming conventions.
    
PLUGIN FILE STRUCTURE:
EXISTING WP STRUCTURE:
/wp-content/plugins/plugin-name/

LOCAL CODE POOL where your namespaces and modules live, there are plans for a core overwrite code pool as well:
/wp-content/plugins/plugin-name/local/

NAMESPACES you may have multiple namespaces within a plugin:
/wp-content/plugins/plugin-name/local/namespace/

NAMESPACES INCLUDES:
/wp-content/plugins/plugin-name/local/namespace/includes/

NAMESPACE INCLUDES SRC you may keep namespace php include files here:
/wp-content/plugins/plugin-name/local/namespace/includes/src/

NAMESPACE INCLUDES MYSQL you may keep namespace mysql de/activation files here:
/wp-content/plugins/plugin-name/local/namespace/includes/mysql/

NAMESPACE INCLUDES CSS you may keep namespace css files here:
/wp-content/plugins/plugin-name/local/namespace/includes/css/

NAMESPACE INCLUDES JS you may keep namespace JS files here:
/wp-content/plugins/plugin-name/local/namespace/includes/js/

MODULES you may have multiple modules within a namespace:
/wp-content/plugins/plugin-name/local/namespace/module/

MODULE CONTROLLERS you may have multiple controllers within a module:
/wp-content/plugins/plugin-name/local/namespace/module/controller/

MODULE MODELS you may have multiple models within a module:
/wp-content/plugins/plugin-name/local/namespace/module/model/

MODULE HTML VIEWS you may have multiple html views within a module:
/wp-content/plugins/plugin-name/local/namespace/module/view/html/

MODULE JS VIEWS you may have multiple css files within a module:
/wp-content/plugins/plugin-name/local/namespace/module/view/js/

MODULE CSS VIEWS you may have multiple js files within a module:
/wp-content/plugins/plugin-name/local/namespace/module/view/css/

ORM:
Ajaxmvc ORM draws some syntactical inspiration from Eloquent ORM. It aims to provide the same functionality though works very differently under the hood, 
and also adds a touch more Eloquence - in my humble opinion anyway. Specifically one example is in respect to nesting WHERE or HAVING conditions. Eloquent uses
callbacks to handle nesting which in my opinion can be very counter-intuitive, especially when dealing with complicated nests or having to dynamically generate 
a nest based on user input - which I am sure would be a very convoluted effort in Eloquent. Ajaxmvc offers an eloquent array parsing engine which allows a developer 
to build deeply nested WHERE or HAVING conditions that exactly replicate the SQL languages nesting system, consequently allowing a developer to intuitively write 
SQL nesting logic in native PHP.  

Another exciting feature is the concept of state for a given model, Ajaxmvc ORM draws this inspiration from RedBean PHP ORM :

A model may be in one of two states: physical or logical.
physical state is HIGHLY recommended for production.
logical state is only plausible in production for storing metadata.
logical state is recommended for early development. 
physical state is recommended for late stages of development.

The point of this ORM is to provide the advantages and the flexibility of EAV in development
and the advantages of Traditional DB Schema in production. The ORM can easily
transition models between both states. So if a model is in production you can migrate a copy to your
development server, change state to logical, and modify it just as you would in its original development, just
make sure to change state to physical in your activation class and methods - oh yea we have those too!
All ORM methods save(),destroy(),get() are compatible with both states, as well as all querying methods of the SQL object chain methods
except delete(),insert(), and update() may only be used in a physical state - plans to change this in the future.
Any modifications made to the physical schema of a model when moving to a logical state (traditional EAV) will be 
retained for a later transition back to physical state, including types - yes types, keys, foreign keys, indices, etc. etc. 
When transitioning states all data will be retained as well.

Some other notable features of this ORM are database TRANSACTIONS, Query Return Typing (data will be returned from the database in PHP native type, MySQL DECIMAL will be returned as PHP double not a string)
, and a full set of SQL object chain methods. Eloquent refers to these as query builders.

You can see examples of everything just explained in the /plugins/example-one/local/example-one/frontend/model/example-one.php file of this repository.

THIS IS A WORK IN PROGRESS of which i am the only one who is actively contributing. Please excuse the grammar in this README and the lack of comments and documentation in certain areas of code.
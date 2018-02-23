
<p align="center">
<a href="https://7d-digital.co.uk"><img src="http://7d-digital.co.uk/images/structure/logo.svg" alt="Build Status"></a>
</p>

# laravel-datatables

## Requirements

Laravel datatables makes use of the following:

* Laravel 5.*
* Bootstrap 3.3.7 
* Propel
* jQuery
* jQuery datatables
* Dans app.js framework

## Installation

1. First you must tell composer to include this repository by adding the following line to the repositories array in `composer.json`: 

    `"url": "https://russellgalpin@github.com/7d-digital/laravel-datatables"`

2. Now in your terminal or cmd line, navigate to your project route and type the following line to install the package:

    `composer require 7d-digital/laravel-datatables "dev-master"`

3. You will need to register the included datatable service provider in app.php by adding the following line to `app.php`:

    `SevenD\LaravelDataTables\Providers\ServiceProvider::class,`

## Usage

### The DataTable class:

The package will make the abstract class: `SevenD\LaravelDataTables\Config\DataTableConfig`. This class can be extended and and then instantiated for use in your page controllers.

It's recommended you make a directory in your `app` directory called `DataTables`. Here you could extend the package standard `DataTableConfig` if you wish. An example of this would look like this: 

```php
<?php namespace App\DataTables;

use SevenD\LaravelDataTables\Config\DataTableConfig as BaseDataTableConfig;

abstract class DataTableConfig extends BaseDataTableConfig
{
    // Additional datatable functionality
}
```

Following this you can now create your specific datatable classes in the same directory. Here's what a `UsersDatatable` might look like:
```php
<?php namespace App\DataTables\Users;

use App\DataTables\DataTableConfig;

class UsersDatatable extends DataTableConfig
{
    // Specific table config
}
```

There are 3 methods you must implement from the base DataTableConfig:
* `setUpEndpoint()` - This method must set a route or URI from where the datatable can fetch an instance of itself.

    A typical use case may look like this:

    ```php
    public function setUpEndpoint()
    {
        $this->setEndpoint(path('users.datatable'));
    }
    ```

* `setUpQuery()` - This method must set up the beginning of a propel query excluding the query terminator, as the terminator will be added dynamically based on user interaction from the frontend.

    A typical use case may look like this:

    ```php
    public function setUpQuery()
    {
        $this->setQuery(UserQuery::create()->filterByRemoved(false));
    }
    ```

* `setUpColumns()` - Here is Where you will define the displays for the columns in relation to the model columns php names. 
    
    The Columns are made up off either`SevenD\LaravelDataTables\Columns\Column` or `SevenD\LaravelDataTables\Columns\JoinColumn` classes.

    Using the method `$this->addColumn()` will add columns starting from the left. 

    Here is an example of the common methods that are associated with a Column object:

    ```php
    public function setUpColumns()
    {
        $this->addColumn(
            Column::create('Id')        // - Takes the php name of the query objects column.
                ->setName('Edit')       // - Internal identifier. (is same as column name by default)
                                        // - You can create multiple table columns based on the same column...
                                        // - provided that this name is unique in this table.
                                        
                ->setTitle('Edit')      // - This is the name that will appear in the column title (is same as column name by default)
                ->setSearchable(false)  // - What it says on the tin. (is true by default unless the column type is never searchable. See below)
                ->setWidth(60)          // - Sets the displayed column width in pixels
                ->setRender(
                    new ColumnRender(['template' => '<a href="{{ route("users.user.edit", ["user_id" => $UserObject->getId()]) }}">Edit</a>'])
                )
                // - ColumnRender can be used to change the html within the column from the raw column data if desired.
                // - Inside the template you can access the Propel Object referred to in the row by via "$[ObjectName]Object"
        );
    }
    ```

    Some column types are not searchable by default. These include: `BINARY`, `BLOB`, `BLOB_NATIVE_TYPE`, `BOOLEAN`, `DATE`, and `TIME`.

### Controller implementation:

Next you must define a route and controller method that matches the route you specified in the DataTables  `setUpEndpoint()` method. This will look something like the following:
```php
public function datatable()
{
    return response()->dataTable(new UsersDatatable());
    // 'UsersDatatable' being the datatable class you just made.
}
```

Now the datatable class is ready to be used passed to a view. A controller view method to a page containing the datatable may look like this: 

```php
public function index()
{
    return View::make('users.index', [
        'usersDataTable' => new UsersDatatable()
    ]);
}
```

### Frontend implementation:

The datatable object comes with a method that will populate the basic html of the table.

```html
<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">Users</h3></div>
    <div class="panel-body">
        {!! $usersDataTable->getHtmlWithJson('usersDataTable') !!}
        <!-- parameter should replicate the variable name of the datatable passed to the view -->
    </div>
</div>
```

The datatables require some javascript before they can display any useful information. Datatables makes use of the jQuery datatables plugin. You will need to include this somewhere on your page. Also make sure that you have the jQuery datatable stylesheet to go along with this. They can both be found here: https://cdn.datatables.net.

The script tags at the foot of the page should include the following near the top: 
```javascript
var mailboxesDataTable = app.buildDataTable('usersDataTable');
```
* The parameter should replicate the variable name of the datatable passed to the view.

Assuming you have the custom app library set up correctly you should now have an interactive datatable. 

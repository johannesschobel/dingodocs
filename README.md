# dingodocs
Automatically generate an API documentation for your Laravel Dingo/API application.

## Installation
Install the package via Composer:
``` bash
$ composer require johannesschobel/dingodocs
```

Then publish the `config` file using the following command:

``` bash
php artisan vendor:publish --provider="JohannesSchobel\DingoDocs\DingoDocsServiceProvider" --tag="config"
```

If you want to customize the output of your API Doc file, you may want to publish the `resource` files as well:
``` bash
php artisan vendor:publish --provider="JohannesSchobel\DingoDocs\DingoDocsServiceProvider" --tag="resources"
```
## Getting Started
The package tries to enable developers to automatically generate an API doc file without burdening the developers to 
much. For example, the package tries to read required information from annotations or even from parameters of the 
method to be called.

### Available Annotations
Currently, the package provides features for the following Annotations:

#### Name
A name for the given Endpoint of your API.

#### Description
A description (long text) for the Endpoint of your API.

#### HTTP METHOD
Respective HTTP Method (`GET`, `POST`, ...) to call the Endpoint. 

#### Authentication
If you need to be authenticated in order to call the Endpoint.

#### Group
A name to group Endpoints together (e.g., used within the navigation bar to the left of the generated file).

#### Role
The Role a requestor needs to have in order to call the Endpoint.

#### Exceptions
The Exceptions an Endpoint may throw including HTTP Status and further descriptions.

#### Request
A sample request to call the Endpoint.

#### Response
A sample Response to be returned from the Endpoint.

#### Transformer
The Transformer (as well as its includes) which are provided by this Endpoint.

#### Transient
Indicates, whether this Endpoint is listed in the resulting API Doc.

## Commands
Simply call 
```bash 
php artisan dingodocs:generate
```
to generate the API doc. The generated file is then stored within the `public` folder of your application. 

## Example
Consider the following example, which illustrates how to use this package:

```php
// namespace and lots of use statements here

/**
 * Class FaqController
 * @package App\Http\Controllers
 *
 * @Group("Faqs")
 * @Authentication
 * @Role("Group")
 */
class FaqController extends ApiBaseController
{
    /**
     * Get all Faqs
     *
     * Returns all Faqs.
     *
     * @param Request $request
     * @return Response the result
     *
     * @Authentication("false")
     * @Transformer("\App\Transformers\FaqTransformer")
     */
    public function index(Request $request) {
        $faqs = Faq::all();

        return $this->response->collection($faqs, new FaqTransformer());
    }

    /**
     * Get one Faq
     *
     * Returns one Faq entry.
     *
     * @param Faq $faq to be displayed
     * @return Response the result
     *
     * @Transformer("\App\Transformers\FaqTransformer")
     * @Exceptions({
     *    @Exception("403", description="If the user is not logged in."),
     *    @Exception("400", description="If some other things happen."),
     * })
     */
    public function show(Faq $faq) {
        $user = authenticate(); // imagine, that this method will return a USER object or null!
        if(is_null($user)) {
            // do a fancy exception handling here!
        }
        
        // do another exception handling here.. 
        
        return $this->response->item($faq, new FaqTransformer());
    }
    
    /**
     * Store a new Faq
     * 
     * Add a new Faq entry to the database.
     * 
     * @param Request $request the request to be stored to the database
     * @return Response the result
     */
    public function store(FaqRequest $request) {
        $faq = new Faq();
        $faq->name = $request->input('name');
        $faq->save();

        return $this->response->created();
    }
}
```

As one can see, some annotations may be placed at `Class`-level, while others may be placed at `Method`-level. However,
method-annotations overwrite class-annotations (cf. the `@Authentication` annotation in the example). 

Note that the package will try to resolve the `FaqRequest` parameter from the `store` method. Furthermore, all 
validation rules (e.g., `name => required|string|max:255`) are parsed and automatically appended to the docs.
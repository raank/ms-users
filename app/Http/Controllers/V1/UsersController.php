<?php

namespace App\Http\Controllers\V1;

use App\Models\V1\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\V1\UserRepository;
use App\Http\Controllers\UsersControllerInterface;

/**
 * @OA\Schema(
 *  schema="v1.pagination.links",
 *  type="object",
 *  description="List of Links",
 *  @OA\Property(property="url", type="string", description="URL of Link", example="http://localhost"),
 *  @OA\Property(property="label", type="string", description="Label of Link", example="my-label"),
 *  @OA\Property(property="active", type="boolean", description="Link is active", example=true),
 * )
 * 
 * @OA\Schema(
 *  schema="v1.paginated",
 *  type="object",
 *  description="Response CRUD paginated",
 *  @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
 *  @OA\Property(
 *      property="data",
 *      type="array",
 *      @OA\Items(ref="#/components/schemas/v1.model_user")
 *  ),
 *  @OA\Property(property="current_page", type="integer", description="Current page", example=1),
 *  @OA\Property(property="first_page_url", type="string", description="First page URL", example="http://localhost"),
 *  @OA\Property(property="from", type="integer", description="From start items", example=1),
 *  @OA\Property(property="last_page", type="integer", description="Last Page Number", example=1),
 *  @OA\Property(property="last_page_url", type="string", description="Last page URL", example="http://localhost"),
 *  @OA\Property(
 *      property="links",
 *      type="array",
 *      description="List of Links",
 *      @OA\Items(ref="#/components/schemas/v1.pagination.links")
 *  ),
 *  @OA\Property(property="next_page_url", type="string", description="Next page URL", example="http://localhost"),
 *  @OA\Property(property="path", type="string", description="Path of current URL", example="http://localhost"),
 *  @OA\Property(property="per_page", type="integer", description="Items number per page", example=1),
 *  @OA\Property(property="prev_page_url", type="string", description="Prev page URL", example="http://localhost"),
 *  @OA\Property(property="to", type="integer", description="Items to end page", example=1),
 *  @OA\Property(property="total", type="integer", description="Total of Items", example=1)
 * )
 */
class UsersController extends Controller implements UsersControllerInterface
{
    /**
     * The users repository.
     *
     * @var UserRepository
     */
    protected $repository;

    /**
     * Create a new controller instance.
     *
     * @param UserRepository $repository
     *
     * @return void
     */
    public function __construct(
        UserRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * All users.
     *
     * @OA\Get(
     *  tags={"v1.users"},
     *  path="/v1/users",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(ref="#/components/schemas/v1.paginated")
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="401",
     *      description="You are not authorized for this action",
     *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
     *  )
     * )
     *
     * @inheritDoc
     */
    public function index(Request $request): JsonResponse
    {
        return $this->response(
            $this->repository
                ->all(
                    (int) $request->query
                        ->get('perPage', 10)
                )
        );
    }

    /**
     * Storing a new User.
     *
     * @OA\Post(
     *  tags={"v1.users"},
     *  path="/v1/users",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *              @OA\Property(property="data", ref="#/components/schemas/v1.model_user")
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="401",
     *      description="You are not authorized for this action",
     *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="name", type="string", description="The name of user."),
     *              @OA\Property(property="email", type="string", description="The email of user."),
     *              @OA\Property(property="password", type="string", description="The password of user."),
     *              @OA\Property(property="password_confirmation", type="string", description="The password confirmation."),
     *              @OA\Property(property="username", type="string", description="The username of user."),
     *              @OA\Property(property="document", type="string", description="The document of user."),
     *              required={"name", "email", "username", "password", "password_confirmation"},
     *              example={
     *                  "name": "John Doe",
     *                  "email": "john@doe.com",
     *                  "password": "password123",
     *                  "password_confirmation": "password123",
     *                  "username": "john.doe",
     *                  "document": "12345678"
     *              }
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'type' => $request->get('type', 'default'),
            'active' => $request->get('active', true),
            'password' => $request->get('password', null),
            'remember_token' => $request->get('remember_token', Str::random(32))
        ]);

        $this->validate($request, [
            'name' => ['required', 'string'],
            'username' => ['string', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'document' => ['string'],
            'active' => ['boolean'],
            'password' => ['string', 'confirmed'],
        ]);

        return $this->response(
            $this->repository
                ->store(
                    $request->all()
                )
        );
    }

    /**
     * Show user specified.
     *
     * @OA\Get(
     *  tags={"v1.users"},
     *  path="/v1/users/{_id}",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Parameter(
     *      name="_id",
     *      in="path",
     *      required=true,
     *      description="Identification of User",
     *      example="60aeba949828bb0c57abc123",
     *      @OA\Schema(
     *          type="string"
     *      )
     *  ),
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *              @OA\Property(property="data", ref="#/components/schemas/v1.model_user")
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="401",
     *      description="You are not authorized for this action",
     *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  )
     * )
     *
     * @inheritDoc
     */
    public function show(string $id): JsonResponse
    {
        $this->exists($id, User::class);

        return $this->response(
            $this->repository->find($id)
        );
    }

    /**
     * Update user specified.
     *
     * @OA\Put(
     *  tags={"v1.users"},
     *  path="/v1/users/{_id}",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Parameter(
     *      name="_id",
     *      in="path",
     *      required=true,
     *      description="Identification of User",
     *      example="60aeba949828bb0c57abc123",
     *      @OA\Schema(
     *          type="string"
     *      )
     *  ),
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *              @OA\Property(property="data", ref="#/components/schemas/v1.model_user")
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="401",
     *      description="You are not authorized for this action",
     *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="name", type="string", description="The name of user."),
     *              @OA\Property(property="email", type="string", description="The email of user."),
     *              @OA\Property(property="username", type="string", description="The username of user."),
     *              @OA\Property(property="document", type="string", description="The document of user."),
     *              example={
     *                  "name": "John Doe",
     *                  "email": "john@doe.com",
     *                  "username": "john.doe",
     *                  "document": "12345678"
     *              }
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $this->exists($id, User::class);

        $this->validate($request, [
            'name' => ['string'],
            'username' => ['string', 'unique:users,username,' . $id . ',_id'],
            'email' => ['email', 'unique:users,email,' . $id . ',_id'],
            'document' => ['string'],
            'active' => ['boolean']
        ]);

        $updated = $this->repository
            ->update(
                $id,
                $request->all()
            );
        
        $user = $this->repository->find($id);

        return $this->response(
            $user
        );
    }

    /**
     * Delete user specified.
     *
     * @OA\Delete(
     *  tags={"v1.users"},
     *  path="/v1/users/{_id}",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Parameter(
     *      name="_id",
     *      in="path",
     *      required=true,
     *      description="Identification of User",
     *      example="60aeba949828bb0c57abc123",
     *      @OA\Schema(
     *          type="string"
     *      )
     *  ),
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="401",
     *      description="You are not authorized for this action",
     *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  )
     * )
     *
     * @inheritDoc
     */
    public function destroy(string $id): JsonResponse
    {
        $this->exists($id, User::class);

        $deleted = $this->repository
            ->destroy($id);

        return $this->response(
            compact('deleted')
        );
    }

    /**
     * Searching users User.
     *
     * @OA\Post(
     *  tags={"v1.users"},
     *  path="/v1/users/search",
     *  security={
     *      {"apiToken": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(ref="#/components/schemas/v1.paginated")
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="401",
     *      description="You are not authorized for this action",
     *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="where", type="items", description="Where Condition."),
     *              @OA\Property(property="whereNotNull", type="items", description="Where field not null."),
     *              @OA\Property(property="whereNull", type="items", description="Where field is nullable."),
     *              @OA\Property(property="orderBy", type="items", description="The password confirmation."),
     *              @OA\Property(property="whereBetween", type="items", description="The username of user."),
     *              example={
     *                  "where": {
     *                      {"field_name", "operator", "value"},
     *                      {"name", "LIKE", "john"}
     *                  },
     *                  "whereNotNull": {"field_name"},
     *                  "whereNull": {"field_name"},
     *                  "orderBy": {
     *                      {"field_name": "field_name", "order": "DESC"}
     *                  },
     *                  "whereBetween": {
     *                      {"field_name": {"from_value", "to_value"}}
     *                  }
     *              }
     *          )
     *      )
     *  )
     * )
     *
     * @inheritDoc
     */
    public function search(Request $request): JsonResponse
    {
        return $this->response(
            $this->repository
                ->search($request)
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\UserLists\CreateUserList;
use App\Actions\UserLists\GetUserLists;
use App\DTOs\UserLists\UserListIndexDTO;
use App\DTOs\UserLists\UserListStoreDTO;
use App\Enums\UserListType;
use App\Http\Requests\UserLists\UserListDeleteRequest;
use App\Http\Requests\UserLists\UserListIndexRequest;
use App\Http\Requests\UserLists\UserListStoreRequest;
use App\Http\Resources\UserListResource;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserListController extends Controller
{
    /**
     * Get paginated list of user lists with filtering, sorting and pagination
     *
     * @param  UserListIndexRequest  $request
     * @param  GetUserLists  $action
     * @return AnonymousResourceCollection
     */
    public function index(UserListIndexRequest $request, GetUserLists $action): AnonymousResourceCollection
    {
        $dto = UserListIndexDTO::fromRequest($request);
        $userLists = $action->handle($dto);

        return UserListResource::collection($userLists);
    }

    /**
     * Store a newly created user list
     *
     * @param  UserListStoreRequest  $request
     * @param  CreateUserList  $action
     * @return UserListResource
     * @authenticated
     */
    public function store(UserListStoreRequest $request, CreateUserList $action): UserListResource
    {
        $dto = UserListStoreDTO::fromRequest($request);

        // Check if user list already exists before creating
        $existingList = UserList::forUser($dto->userId)
            ->forListable($dto->listableType, $dto->listableId)
            ->ofType($dto->type)
            ->first();

        $userList = $action->handle($dto);
        $statusCode = $existingList ? 200 : 201; // 200 for update, 201 for create

        return (new UserListResource($userList->load(['user', 'listable'])))
            ->response()
            ->setStatusCode($statusCode);
    }

    /**
     * Get detailed information about a specific user list
     *
     * @param  UserList  $userList
     * @return UserListResource
     */
    public function show(UserList $userList): UserListResource
    {
        return new UserListResource($userList->load(['user', 'listable']));
    }

    /**
     * Remove the specified user list
     *
     * @param  UserListDeleteRequest  $request
     * @param  UserList  $userList
     * @return JsonResponse
     * @authenticated
     */
    public function destroy(UserListDeleteRequest $request, UserList $userList): JsonResponse
    {
        $userList->delete();

        return response()->json(['message' => 'User list deleted successfully']);
    }

    /**
     * Get user lists for a specific user
     *
     * @param  User  $user
     * @param  UserListIndexRequest  $request
     * @param  GetUserLists  $action
     * @return AnonymousResourceCollection
     */
    public function forUser(User $user, UserListIndexRequest $request, GetUserLists $action): AnonymousResourceCollection
    {
        $request->merge(['user_id' => $user->id]);

        // Якщо користувач має приватні улюблені і це не власник і не адмін
        if ($user->is_private_favorites && auth()->check() && auth()->id() !== $user->id && !auth()->user()->isAdmin()) {
            // Виключаємо улюблені зі списку
            $request->merge(['exclude_types' => [UserListType::FAVORITE->value]]);
        } elseif ($user->is_private_favorites && !auth()->check()) {
            // Якщо користувач не авторизований, також виключаємо улюблені
            $request->merge(['exclude_types' => [UserListType::FAVORITE->value]]);
        }

        $dto = UserListIndexDTO::fromRequest($request);
        $userLists = $action->handle($dto);

        return UserListResource::collection($userLists);
    }

    /**
     * Get user lists by type
     *
     * @param  string  $type
     * @param  UserListIndexRequest  $request
     * @param  GetUserLists  $action
     * @return AnonymousResourceCollection
     *
     * @urlParam type required The type of user list. Example: favorite, watching
     */
    public function byType(string $type, UserListIndexRequest $request, GetUserLists $action): AnonymousResourceCollection
    {
        $request->merge(['types' => [UserListType::from($type)->value]]);
        $dto = UserListIndexDTO::fromRequest($request);
        $userLists = $action->handle($dto);

        return UserListResource::collection($userLists);
    }
}

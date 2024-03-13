<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FollowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'follower_id' => $this->user_id,
            'followed_user_id' => $this->followeduser,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Include user details using UserResource if necessary
            'follower' => new UserResource($this->whenLoaded('follower')),
            'followedUser' => new UserResource($this->whenLoaded('followedUser')),
        ];
    }
}

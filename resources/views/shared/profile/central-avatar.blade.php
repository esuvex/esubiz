{{-- ESUBIZ_CENTRAL_PROFILE_AVATAR_V18 --}}
@php
    $centralAvatarUser =
        $user
        ?? auth()->user();

    $centralAvatarName = trim(
        (string) (
            $centralAvatarUser?->name
            ?? 'User'
        )
    );

    $centralAvatarInitials = strtoupper(
        mb_substr(
            $centralAvatarName !== ''
                ? $centralAvatarName
                : 'U',
            0,
            2
        )
    );

    $centralAvatarHasPhoto =
        $centralAvatarUser
        && !empty(
            $centralAvatarUser->profile_photo_path
        );
@endphp

@if($centralAvatarHasPhoto)
    <img
        src="{{ route(
            'central.profile.photo',
            [
                'v' => optional(
                    $centralAvatarUser->updated_at
                )->timestamp,
            ]
        ) }}"
        alt="{{ $centralAvatarName }}"
        class="h-full w-full rounded-full object-cover"
    >
@else
    <span>
        {{ $centralAvatarInitials }}
    </span>
@endif

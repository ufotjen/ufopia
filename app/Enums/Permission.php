<?php

namespace App\Enums;

enum Permission: string
{
    // Admin & rollen
    case AdminView                = 'admin.view';
    case AdminManage              = 'admin.manage';
    case RolesAssign              = 'roles.assign';
    case RolesManage              = 'roles.manage';
    case AdminsCreate             = 'admins.create';
    case AdminsDeleteSelfOrLower  = 'admins.delete_self_or_lower';
    case SuperAdminTransfer       = 'superadmin.transfer';

    // Users
    case UsersView                = 'users.view';
    case UsersCreate              = 'users.create';
    case UsersUpdate              = 'users.update';
    case UsersDeactivate          = 'users.deactivate';
    case UsersSuspend             = 'users.suspend';
    case UsersDeleteSoft          = 'users.delete_soft';
    case UsersNotify              = 'users.notify';

    // Content (posts/topics/pages)
    case PostsRead                = 'posts.read';
    case PostsCreate              = 'posts.create';
    case PostsUpdateOwn           = 'posts.update.own';
    case PostsDeleteOwn           = 'posts.delete.own';
    case PostsUpdateAny           = 'posts.update.any';
    case PostsDeleteAny           = 'posts.delete.any';
    case PostsSetInactive         = 'posts.set_inactive';
    case PostsReview              = 'posts.review';
    case PostsFlag                = 'posts.flag';
    case PostsFlagsResolve        = 'posts.flags.resolve';

    // Social
    case ProfileUpdateSelf        = 'profile.update.self';
    case ProfileDeactivateSelf    = 'profile.deactivate.self';
    case ProfileDeleteSelf        = 'profile.delete.self';
    case FriendshipsSend          = 'friendships.send';
    case MessagesSend             = 'messages.send';
}

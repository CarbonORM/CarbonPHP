import {
    iPostC6RestResponse,
    restRequest,
    GET,
    POST,
    PUT,
    DELETE,
    iDeleteC6RestResponse,
    iGetC6RestResponse,
    iPutC6RestResponse,
    removeInvalidKeys
} from "@carbonorm/carbonnode";
import {deleteRestfulObjectArrays, updateRestfulObjectArrays} from "@carbonorm/carbonreact";
import {C6, iUser_Followers, user_followers, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iUser_Followers, {}, iGetC6RestResponse<iUser_Followers>, RestShortTableNames>({
    C6: C6,
    tableName: user_followers.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received user followers!'
        request.error ??= 'An unknown issue occurred creating the user followers!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iUser_Followers>(response?.data?.rest, "user_followers", C6.user_followers.PRIMARY_SHORT as (keyof iUser_Followers)[])
    }
})

export const Put = restRequest<{}, iUser_Followers, {}, iPutC6RestResponse<iUser_Followers>, RestShortTableNames>({
    C6: C6,
    tableName: user_followers.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated user followers!'
        request.error ??= 'An unknown issue occurred updating the user followers!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iUser_Followers>([
            removeInvalidKeys<iUser_Followers>(response?.data?.rest, C6.TABLES)
        ], "user_followers", user_followers.PRIMARY_SHORT as (keyof iUser_Followers)[])
    }
})


export const Post = restRequest<{}, iUser_Followers, {}, iPostC6RestResponse<iUser_Followers>, RestShortTableNames>({
    C6: C6,
    tableName: user_followers.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the user followers!'
        request.error ??= 'An unknown issue occurred creating the user followers!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iUser_Followers>([
            removeInvalidKeys<iUser_Followers>(response?.data?.rest, C6.TABLES)
        ], "user_followers", user_followers.PRIMARY_SHORT as (keyof iUser_Followers[])
    }
})

export const Delete = restRequest<{}, iUser_Followers, {}, iDeleteC6RestResponse<iUser_Followers>, RestShortTableNames>(
    {
        C6: C6,
        tableName: user_followers.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the user followers!'
            request.error ??= 'An unknown issue occurred removing the user followers!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iUser_Followers>([
                request
            ], "user_followers", user_followers.PRIMARY_SHORT as (keyof iUser_Followers)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

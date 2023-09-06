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
import {C6, iUsers, users, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iUsers, {}, iGetC6RestResponse<iUsers>, RestShortTableNames>({
    C6: C6,
    tableName: users.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received users!'
        request.error ??= 'An unknown issue occurred creating the users!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iUsers>(response?.data?.rest, "users", C6.users.PRIMARY_SHORT as (keyof iUsers)[])
    }
})

export const Put = restRequest<{}, iUsers, {}, iPutC6RestResponse<iUsers>, RestShortTableNames>({
    C6: C6,
    tableName: users.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated users!'
        request.error ??= 'An unknown issue occurred updating the users!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iUsers>([
            removeInvalidKeys<iUsers>(response?.data?.rest, C6.TABLES)
        ], "users", users.PRIMARY_SHORT as (keyof iUsers)[])
    }
})


export const Post = restRequest<{}, iUsers, {}, iPostC6RestResponse<iUsers>, RestShortTableNames>({
    C6: C6,
    tableName: users.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the users!'
        request.error ??= 'An unknown issue occurred creating the users!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iUsers>([
            removeInvalidKeys<iUsers>(response?.data?.rest, C6.TABLES)
        ], "users", users.PRIMARY_SHORT as (keyof iUsers[])
    }
})

export const Delete = restRequest<{}, iUsers, {}, iDeleteC6RestResponse<iUsers>, RestShortTableNames>(
    {
        C6: C6,
        tableName: users.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the users!'
            request.error ??= 'An unknown issue occurred removing the users!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iUsers>([
                request
            ], "users", users.PRIMARY_SHORT as (keyof iUsers)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

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
import {C6, iUser_Groups, user_groups, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iUser_Groups, {}, iGetC6RestResponse<iUser_Groups>, RestShortTableNames>({
    C6: C6,
    tableName: user_groups.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received user groups!'
        request.error ??= 'An unknown issue occurred creating the user groups!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iUser_Groups>(response?.data?.rest, "user_groups", C6.user_groups.PRIMARY_SHORT as (keyof iUser_Groups)[])
    }
})

export const Put = restRequest<{}, iUser_Groups, {}, iPutC6RestResponse<iUser_Groups>, RestShortTableNames>({
    C6: C6,
    tableName: user_groups.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated user groups!'
        request.error ??= 'An unknown issue occurred updating the user groups!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iUser_Groups>([
            removeInvalidKeys<iUser_Groups>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "user_groups", user_groups.PRIMARY_SHORT as (keyof iUser_Groups)[])
    }
})


export const Post = restRequest<{}, iUser_Groups, {}, iPostC6RestResponse<iUser_Groups>, RestShortTableNames>({
    C6: C6,
    tableName: user_groups.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the user groups!'
        request.error ??= 'An unknown issue occurred creating the user groups!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== user_groups.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[user_groups.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iUser_Groups>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iUser_Groups>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iUser_Groups>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "user_groups", user_groups.PRIMARY_SHORT as (keyof iUser_Groups)[])
    }
})

export const Delete = restRequest<{}, iUser_Groups, {}, iDeleteC6RestResponse<iUser_Groups>, RestShortTableNames>(
    {
        C6: C6,
        tableName: user_groups.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the user groups!'
            request.error ??= 'An unknown issue occurred removing the user groups!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iUser_Groups>([
                request
            ], "user_groups", user_groups.PRIMARY_SHORT as (keyof iUser_Groups)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

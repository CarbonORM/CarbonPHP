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
import {C6, iUser_Tasks, user_tasks, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iUser_Tasks, {}, iGetC6RestResponse<iUser_Tasks>, RestShortTableNames>({
    C6: C6,
    tableName: user_tasks.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received user tasks!'
        request.error ??= 'An unknown issue occurred creating the user tasks!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iUser_Tasks>(response?.data?.rest, "user_tasks", C6.user_tasks.PRIMARY_SHORT as (keyof iUser_Tasks)[])
    }
})

export const Put = restRequest<{}, iUser_Tasks, {}, iPutC6RestResponse<iUser_Tasks>, RestShortTableNames>({
    C6: C6,
    tableName: user_tasks.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated user tasks!'
        request.error ??= 'An unknown issue occurred updating the user tasks!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iUser_Tasks>([
            removeInvalidKeys<iUser_Tasks>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "user_tasks", user_tasks.PRIMARY_SHORT as (keyof iUser_Tasks)[])
    }
})


export const Post = restRequest<{}, iUser_Tasks, {}, iPostC6RestResponse<iUser_Tasks>, RestShortTableNames>({
    C6: C6,
    tableName: user_tasks.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the user tasks!'
        request.error ??= 'An unknown issue occurred creating the user tasks!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== user_tasks.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[user_tasks.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iUser_Tasks>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iUser_Tasks>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iUser_Tasks>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "user_tasks", user_tasks.PRIMARY_SHORT as (keyof iUser_Tasks)[])
    }
})

export const Delete = restRequest<{}, iUser_Tasks, {}, iDeleteC6RestResponse<iUser_Tasks>, RestShortTableNames>(
    {
        C6: C6,
        tableName: user_tasks.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the user tasks!'
            request.error ??= 'An unknown issue occurred removing the user tasks!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iUser_Tasks>([
                request
            ], "user_tasks", user_tasks.PRIMARY_SHORT as (keyof iUser_Tasks)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

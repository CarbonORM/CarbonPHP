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
import {C6, iUser_Sessions, user_sessions, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iUser_Sessions, {}, iGetC6RestResponse<iUser_Sessions>, RestShortTableNames>({
    C6: C6,
    tableName: user_sessions.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received user sessions!'
        request.error ??= 'An unknown issue occurred creating the user sessions!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iUser_Sessions>(response?.data?.rest, "user_sessions", C6.user_sessions.PRIMARY_SHORT as (keyof iUser_Sessions)[])
    }
})

export const Put = restRequest<{}, iUser_Sessions, {}, iPutC6RestResponse<iUser_Sessions>, RestShortTableNames>({
    C6: C6,
    tableName: user_sessions.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated user sessions!'
        request.error ??= 'An unknown issue occurred updating the user sessions!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iUser_Sessions>([
            removeInvalidKeys<iUser_Sessions>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "user_sessions", user_sessions.PRIMARY_SHORT as (keyof iUser_Sessions)[])
    }
})


export const Post = restRequest<{}, iUser_Sessions, {}, iPostC6RestResponse<iUser_Sessions>, RestShortTableNames>({
    C6: C6,
    tableName: user_sessions.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the user sessions!'
        request.error ??= 'An unknown issue occurred creating the user sessions!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== user_sessions.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[user_sessions.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iUser_Sessions>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iUser_Sessions>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iUser_Sessions>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "user_sessions", user_sessions.PRIMARY_SHORT as (keyof iUser_Sessions)[])
    }
})

export const Delete = restRequest<{}, iUser_Sessions, {}, iDeleteC6RestResponse<iUser_Sessions>, RestShortTableNames>(
    {
        C6: C6,
        tableName: user_sessions.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the user sessions!'
            request.error ??= 'An unknown issue occurred removing the user sessions!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iUser_Sessions>([
                request
            ], "user_sessions", user_sessions.PRIMARY_SHORT as (keyof iUser_Sessions)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

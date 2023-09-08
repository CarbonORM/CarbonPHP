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
import {C6, iUser_Messages, user_messages, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iUser_Messages, {}, iGetC6RestResponse<iUser_Messages>, RestShortTableNames>({
    C6: C6,
    tableName: user_messages.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received user messages!'
        request.error ??= 'An unknown issue occurred creating the user messages!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iUser_Messages>(response?.data?.rest, "user_messages", C6.user_messages.PRIMARY_SHORT as (keyof iUser_Messages)[])
    }
})

export const Put = restRequest<{}, iUser_Messages, {}, iPutC6RestResponse<iUser_Messages>, RestShortTableNames>({
    C6: C6,
    tableName: user_messages.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated user messages!'
        request.error ??= 'An unknown issue occurred updating the user messages!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iUser_Messages>([
            removeInvalidKeys<iUser_Messages>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "user_messages", user_messages.PRIMARY_SHORT as (keyof iUser_Messages)[])
    }
})


export const Post = restRequest<{}, iUser_Messages, {}, iPostC6RestResponse<iUser_Messages>, RestShortTableNames>({
    C6: C6,
    tableName: user_messages.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the user messages!'
        request.error ??= 'An unknown issue occurred creating the user messages!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== user_messages.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[user_messages.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iUser_Messages>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iUser_Messages>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iUser_Messages>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "user_messages", user_messages.PRIMARY_SHORT as (keyof iUser_Messages)[])
    }
})

export const Delete = restRequest<{}, iUser_Messages, {}, iDeleteC6RestResponse<iUser_Messages>, RestShortTableNames>(
    {
        C6: C6,
        tableName: user_messages.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the user messages!'
            request.error ??= 'An unknown issue occurred removing the user messages!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iUser_Messages>([
                request
            ], "user_messages", user_messages.PRIMARY_SHORT as (keyof iUser_Messages)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

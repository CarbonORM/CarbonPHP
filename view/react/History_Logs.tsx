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
import {C6, iHistory_Logs, history_logs, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iHistory_Logs, {}, iGetC6RestResponse<iHistory_Logs>, RestShortTableNames>({
    C6: C6,
    tableName: history_logs.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received history logs!'
        request.error ??= 'An unknown issue occurred creating the history logs!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iHistory_Logs>(response?.data?.rest, "history_logs", C6.history_logs.PRIMARY_SHORT as (keyof iHistory_Logs)[])
    }
})

export const Put = restRequest<{}, iHistory_Logs, {}, iPutC6RestResponse<iHistory_Logs>, RestShortTableNames>({
    C6: C6,
    tableName: history_logs.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated history logs!'
        request.error ??= 'An unknown issue occurred updating the history logs!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iHistory_Logs>([
            removeInvalidKeys<iHistory_Logs>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "history_logs", history_logs.PRIMARY_SHORT as (keyof iHistory_Logs)[])
    }
})


export const Post = restRequest<{}, iHistory_Logs, {}, iPostC6RestResponse<iHistory_Logs>, RestShortTableNames>({
    C6: C6,
    tableName: history_logs.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the history logs!'
        request.error ??= 'An unknown issue occurred creating the history logs!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== history_logs.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[history_logs.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iHistory_Logs>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iHistory_Logs>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iHistory_Logs>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "history_logs", history_logs.PRIMARY_SHORT as (keyof iHistory_Logs)[])
    }
})

export const Delete = restRequest<{}, iHistory_Logs, {}, iDeleteC6RestResponse<iHistory_Logs>, RestShortTableNames>(
    {
        C6: C6,
        tableName: history_logs.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the history logs!'
            request.error ??= 'An unknown issue occurred removing the history logs!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iHistory_Logs>([
                request
            ], "history_logs", history_logs.PRIMARY_SHORT as (keyof iHistory_Logs)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

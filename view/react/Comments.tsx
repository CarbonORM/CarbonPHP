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
import {C6, iComments, comments, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iComments, {}, iGetC6RestResponse<iComments>, RestShortTableNames>({
    C6: C6,
    tableName: comments.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received comments!'
        request.error ??= 'An unknown issue occurred creating the comments!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iComments>(response?.data?.rest, "comments", C6.comments.PRIMARY_SHORT as (keyof iComments)[])
    }
})

export const Put = restRequest<{}, iComments, {}, iPutC6RestResponse<iComments>, RestShortTableNames>({
    C6: C6,
    tableName: comments.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated comments!'
        request.error ??= 'An unknown issue occurred updating the comments!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iComments>([
            removeInvalidKeys<iComments>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "comments", comments.PRIMARY_SHORT as (keyof iComments)[])
    }
})


export const Post = restRequest<{}, iComments, {}, iPostC6RestResponse<iComments>, RestShortTableNames>({
    C6: C6,
    tableName: comments.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the comments!'
        request.error ??= 'An unknown issue occurred creating the comments!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== comments.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[comments.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iComments>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iComments>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iComments>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "comments", comments.PRIMARY_SHORT as (keyof iComments)[])
    }
})

export const Delete = restRequest<{}, iComments, {}, iDeleteC6RestResponse<iComments>, RestShortTableNames>(
    {
        C6: C6,
        tableName: comments.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the comments!'
            request.error ??= 'An unknown issue occurred removing the comments!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iComments>([
                request
            ], "comments", comments.PRIMARY_SHORT as (keyof iComments)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

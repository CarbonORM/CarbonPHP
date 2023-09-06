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
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iComments>([
            removeInvalidKeys<iComments>(response?.data?.rest, C6.TABLES)
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
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iComments>([
            removeInvalidKeys<iComments>(response?.data?.rest, C6.TABLES)
        ], "comments", comments.PRIMARY_SHORT as (keyof iComments[])
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

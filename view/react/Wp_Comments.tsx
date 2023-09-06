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
import {C6, iWp_Comments, wp_comments, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Comments, {}, iGetC6RestResponse<iWp_Comments>, RestShortTableNames>({
    C6: C6,
    tableName: wp_comments.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp comments!'
        request.error ??= 'An unknown issue occurred creating the wp comments!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Comments>(response?.data?.rest, "wp_comments", C6.wp_comments.PRIMARY_SHORT as (keyof iWp_Comments)[])
    }
})

export const Put = restRequest<{}, iWp_Comments, {}, iPutC6RestResponse<iWp_Comments>, RestShortTableNames>({
    C6: C6,
    tableName: wp_comments.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp comments!'
        request.error ??= 'An unknown issue occurred updating the wp comments!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Comments>([
            removeInvalidKeys<iWp_Comments>(response?.data?.rest, C6.TABLES)
        ], "wp_comments", wp_comments.PRIMARY_SHORT as (keyof iWp_Comments)[])
    }
})


export const Post = restRequest<{}, iWp_Comments, {}, iPostC6RestResponse<iWp_Comments>, RestShortTableNames>({
    C6: C6,
    tableName: wp_comments.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp comments!'
        request.error ??= 'An unknown issue occurred creating the wp comments!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Comments>([
            removeInvalidKeys<iWp_Comments>(response?.data?.rest, C6.TABLES)
        ], "wp_comments", wp_comments.PRIMARY_SHORT as (keyof iWp_Comments[])
    }
})

export const Delete = restRequest<{}, iWp_Comments, {}, iDeleteC6RestResponse<iWp_Comments>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_comments.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp comments!'
            request.error ??= 'An unknown issue occurred removing the wp comments!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Comments>([
                request
            ], "wp_comments", wp_comments.PRIMARY_SHORT as (keyof iWp_Comments)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

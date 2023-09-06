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
import {C6, iWp_Commentmeta, wp_commentmeta, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Commentmeta, {}, iGetC6RestResponse<iWp_Commentmeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_commentmeta.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp commentmeta!'
        request.error ??= 'An unknown issue occurred creating the wp commentmeta!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Commentmeta>(response?.data?.rest, "wp_commentmeta", C6.wp_commentmeta.PRIMARY_SHORT as (keyof iWp_Commentmeta)[])
    }
})

export const Put = restRequest<{}, iWp_Commentmeta, {}, iPutC6RestResponse<iWp_Commentmeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_commentmeta.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp commentmeta!'
        request.error ??= 'An unknown issue occurred updating the wp commentmeta!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Commentmeta>([
            removeInvalidKeys<iWp_Commentmeta>(response?.data?.rest, C6.TABLES)
        ], "wp_commentmeta", wp_commentmeta.PRIMARY_SHORT as (keyof iWp_Commentmeta)[])
    }
})


export const Post = restRequest<{}, iWp_Commentmeta, {}, iPostC6RestResponse<iWp_Commentmeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_commentmeta.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp commentmeta!'
        request.error ??= 'An unknown issue occurred creating the wp commentmeta!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Commentmeta>([
            removeInvalidKeys<iWp_Commentmeta>(response?.data?.rest, C6.TABLES)
        ], "wp_commentmeta", wp_commentmeta.PRIMARY_SHORT as (keyof iWp_Commentmeta[])
    }
})

export const Delete = restRequest<{}, iWp_Commentmeta, {}, iDeleteC6RestResponse<iWp_Commentmeta>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_commentmeta.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp commentmeta!'
            request.error ??= 'An unknown issue occurred removing the wp commentmeta!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Commentmeta>([
                request
            ], "wp_commentmeta", wp_commentmeta.PRIMARY_SHORT as (keyof iWp_Commentmeta)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

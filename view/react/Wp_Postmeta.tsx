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
import {C6, iWp_Postmeta, wp_postmeta, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Postmeta, {}, iGetC6RestResponse<iWp_Postmeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_postmeta.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp postmeta!'
        request.error ??= 'An unknown issue occurred creating the wp postmeta!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Postmeta>(response?.data?.rest, "wp_postmeta", C6.wp_postmeta.PRIMARY_SHORT as (keyof iWp_Postmeta)[])
    }
})

export const Put = restRequest<{}, iWp_Postmeta, {}, iPutC6RestResponse<iWp_Postmeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_postmeta.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp postmeta!'
        request.error ??= 'An unknown issue occurred updating the wp postmeta!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Postmeta>([
            removeInvalidKeys<iWp_Postmeta>(response?.data?.rest, C6.TABLES)
        ], "wp_postmeta", wp_postmeta.PRIMARY_SHORT as (keyof iWp_Postmeta)[])
    }
})


export const Post = restRequest<{}, iWp_Postmeta, {}, iPostC6RestResponse<iWp_Postmeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_postmeta.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp postmeta!'
        request.error ??= 'An unknown issue occurred creating the wp postmeta!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Postmeta>([
            removeInvalidKeys<iWp_Postmeta>(response?.data?.rest, C6.TABLES)
        ], "wp_postmeta", wp_postmeta.PRIMARY_SHORT as (keyof iWp_Postmeta[])
    }
})

export const Delete = restRequest<{}, iWp_Postmeta, {}, iDeleteC6RestResponse<iWp_Postmeta>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_postmeta.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp postmeta!'
            request.error ??= 'An unknown issue occurred removing the wp postmeta!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Postmeta>([
                request
            ], "wp_postmeta", wp_postmeta.PRIMARY_SHORT as (keyof iWp_Postmeta)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

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
import {C6, iWp_Term_Relationships, wp_term_relationships, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Term_Relationships, {}, iGetC6RestResponse<iWp_Term_Relationships>, RestShortTableNames>({
    C6: C6,
    tableName: wp_term_relationships.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp term relationships!'
        request.error ??= 'An unknown issue occurred creating the wp term relationships!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Term_Relationships>(response?.data?.rest, "wp_term_relationships", C6.wp_term_relationships.PRIMARY_SHORT as (keyof iWp_Term_Relationships)[])
    }
})

export const Put = restRequest<{}, iWp_Term_Relationships, {}, iPutC6RestResponse<iWp_Term_Relationships>, RestShortTableNames>({
    C6: C6,
    tableName: wp_term_relationships.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp term relationships!'
        request.error ??= 'An unknown issue occurred updating the wp term relationships!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Term_Relationships>([
            removeInvalidKeys<iWp_Term_Relationships>(response?.data?.rest, C6.TABLES)
        ], "wp_term_relationships", wp_term_relationships.PRIMARY_SHORT as (keyof iWp_Term_Relationships)[])
    }
})


export const Post = restRequest<{}, iWp_Term_Relationships, {}, iPostC6RestResponse<iWp_Term_Relationships>, RestShortTableNames>({
    C6: C6,
    tableName: wp_term_relationships.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp term relationships!'
        request.error ??= 'An unknown issue occurred creating the wp term relationships!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Term_Relationships>([
            removeInvalidKeys<iWp_Term_Relationships>(response?.data?.rest, C6.TABLES)
        ], "wp_term_relationships", wp_term_relationships.PRIMARY_SHORT as (keyof iWp_Term_Relationships[])
    }
})

export const Delete = restRequest<{}, iWp_Term_Relationships, {}, iDeleteC6RestResponse<iWp_Term_Relationships>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_term_relationships.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp term relationships!'
            request.error ??= 'An unknown issue occurred removing the wp term relationships!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Term_Relationships>([
                request
            ], "wp_term_relationships", wp_term_relationships.PRIMARY_SHORT as (keyof iWp_Term_Relationships)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

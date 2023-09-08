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
import {C6, iWp_Term_Taxonomy, wp_term_taxonomy, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Term_Taxonomy, {}, iGetC6RestResponse<iWp_Term_Taxonomy>, RestShortTableNames>({
    C6: C6,
    tableName: wp_term_taxonomy.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp term taxonomy!'
        request.error ??= 'An unknown issue occurred creating the wp term taxonomy!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Term_Taxonomy>(response?.data?.rest, "wp_term_taxonomy", C6.wp_term_taxonomy.PRIMARY_SHORT as (keyof iWp_Term_Taxonomy)[])
    }
})

export const Put = restRequest<{}, iWp_Term_Taxonomy, {}, iPutC6RestResponse<iWp_Term_Taxonomy>, RestShortTableNames>({
    C6: C6,
    tableName: wp_term_taxonomy.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp term taxonomy!'
        request.error ??= 'An unknown issue occurred updating the wp term taxonomy!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iWp_Term_Taxonomy>([
            removeInvalidKeys<iWp_Term_Taxonomy>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "wp_term_taxonomy", wp_term_taxonomy.PRIMARY_SHORT as (keyof iWp_Term_Taxonomy)[])
    }
})


export const Post = restRequest<{}, iWp_Term_Taxonomy, {}, iPostC6RestResponse<iWp_Term_Taxonomy>, RestShortTableNames>({
    C6: C6,
    tableName: wp_term_taxonomy.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp term taxonomy!'
        request.error ??= 'An unknown issue occurred creating the wp term taxonomy!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== wp_term_taxonomy.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[wp_term_taxonomy.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iWp_Term_Taxonomy>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iWp_Term_Taxonomy>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iWp_Term_Taxonomy>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "wp_term_taxonomy", wp_term_taxonomy.PRIMARY_SHORT as (keyof iWp_Term_Taxonomy)[])
    }
})

export const Delete = restRequest<{}, iWp_Term_Taxonomy, {}, iDeleteC6RestResponse<iWp_Term_Taxonomy>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_term_taxonomy.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp term taxonomy!'
            request.error ??= 'An unknown issue occurred removing the wp term taxonomy!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Term_Taxonomy>([
                request
            ], "wp_term_taxonomy", wp_term_taxonomy.PRIMARY_SHORT as (keyof iWp_Term_Taxonomy)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

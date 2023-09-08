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
import {C6, iWp_Posts, wp_posts, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Posts, {}, iGetC6RestResponse<iWp_Posts>, RestShortTableNames>({
    C6: C6,
    tableName: wp_posts.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp posts!'
        request.error ??= 'An unknown issue occurred creating the wp posts!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Posts>(response?.data?.rest, "wp_posts", C6.wp_posts.PRIMARY_SHORT as (keyof iWp_Posts)[])
    }
})

export const Put = restRequest<{}, iWp_Posts, {}, iPutC6RestResponse<iWp_Posts>, RestShortTableNames>({
    C6: C6,
    tableName: wp_posts.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp posts!'
        request.error ??= 'An unknown issue occurred updating the wp posts!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iWp_Posts>([
            removeInvalidKeys<iWp_Posts>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "wp_posts", wp_posts.PRIMARY_SHORT as (keyof iWp_Posts)[])
    }
})


export const Post = restRequest<{}, iWp_Posts, {}, iPostC6RestResponse<iWp_Posts>, RestShortTableNames>({
    C6: C6,
    tableName: wp_posts.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp posts!'
        request.error ??= 'An unknown issue occurred creating the wp posts!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== wp_posts.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[wp_posts.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iWp_Posts>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iWp_Posts>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iWp_Posts>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "wp_posts", wp_posts.PRIMARY_SHORT as (keyof iWp_Posts)[])
    }
})

export const Delete = restRequest<{}, iWp_Posts, {}, iDeleteC6RestResponse<iWp_Posts>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_posts.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp posts!'
            request.error ??= 'An unknown issue occurred removing the wp posts!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Posts>([
                request
            ], "wp_posts", wp_posts.PRIMARY_SHORT as (keyof iWp_Posts)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

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
import {C6, iWp_Links, wp_links, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Links, {}, iGetC6RestResponse<iWp_Links>, RestShortTableNames>({
    C6: C6,
    tableName: wp_links.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp links!'
        request.error ??= 'An unknown issue occurred creating the wp links!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Links>(response?.data?.rest, "wp_links", C6.wp_links.PRIMARY_SHORT as (keyof iWp_Links)[])
    }
})

export const Put = restRequest<{}, iWp_Links, {}, iPutC6RestResponse<iWp_Links>, RestShortTableNames>({
    C6: C6,
    tableName: wp_links.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp links!'
        request.error ??= 'An unknown issue occurred updating the wp links!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iWp_Links>([
            removeInvalidKeys<iWp_Links>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "wp_links", wp_links.PRIMARY_SHORT as (keyof iWp_Links)[])
    }
})


export const Post = restRequest<{}, iWp_Links, {}, iPostC6RestResponse<iWp_Links>, RestShortTableNames>({
    C6: C6,
    tableName: wp_links.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp links!'
        request.error ??= 'An unknown issue occurred creating the wp links!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== wp_links.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[wp_links.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iWp_Links>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iWp_Links>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iWp_Links>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "wp_links", wp_links.PRIMARY_SHORT as (keyof iWp_Links)[])
    }
})

export const Delete = restRequest<{}, iWp_Links, {}, iDeleteC6RestResponse<iWp_Links>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_links.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp links!'
            request.error ??= 'An unknown issue occurred removing the wp links!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Links>([
                request
            ], "wp_links", wp_links.PRIMARY_SHORT as (keyof iWp_Links)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

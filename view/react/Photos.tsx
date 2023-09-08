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
import {C6, iPhotos, photos, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iPhotos, {}, iGetC6RestResponse<iPhotos>, RestShortTableNames>({
    C6: C6,
    tableName: photos.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received photos!'
        request.error ??= 'An unknown issue occurred creating the photos!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iPhotos>(response?.data?.rest, "photos", C6.photos.PRIMARY_SHORT as (keyof iPhotos)[])
    }
})

export const Put = restRequest<{}, iPhotos, {}, iPutC6RestResponse<iPhotos>, RestShortTableNames>({
    C6: C6,
    tableName: photos.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated photos!'
        request.error ??= 'An unknown issue occurred updating the photos!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iPhotos>([
            removeInvalidKeys<iPhotos>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "photos", photos.PRIMARY_SHORT as (keyof iPhotos)[])
    }
})


export const Post = restRequest<{}, iPhotos, {}, iPostC6RestResponse<iPhotos>, RestShortTableNames>({
    C6: C6,
    tableName: photos.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the photos!'
        request.error ??= 'An unknown issue occurred creating the photos!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== photos.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[photos.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iPhotos>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iPhotos>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iPhotos>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "photos", photos.PRIMARY_SHORT as (keyof iPhotos)[])
    }
})

export const Delete = restRequest<{}, iPhotos, {}, iDeleteC6RestResponse<iPhotos>, RestShortTableNames>(
    {
        C6: C6,
        tableName: photos.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the photos!'
            request.error ??= 'An unknown issue occurred removing the photos!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iPhotos>([
                request
            ], "photos", photos.PRIMARY_SHORT as (keyof iPhotos)[])
        }
    });


export default {
    Get, Post, Put, Delete
}

function(doc) {
    if (doc.type == 'Doctrine.Tests.Models.CMS.CmsUser') {
        emit(doc.username, doc._id);
    }
}